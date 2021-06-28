<?php

namespace Gesof\MakerBundle\Helper;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Symfony\Bundle\MakerBundle\Doctrine\EntityDetails;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Component\Console\Style\SymfonyStyle;

use Gesof\MakerBundle\Doctrine\DoctrineHelper;

/**
 *
 */
class CrudHelper
{
    /** @var Inflector */
    private $inflector;
    /** @var FileManager */
    protected $fileManager;
    /** @var DoctrineHelper */
    protected $doctrineHelper;
    /** @var SymfonyStyle */
    protected $io;
    /** @var Generator */
    protected $generator;
    /** @var string */
    protected $projectDir;
    /** @var string */
    private $skeletonDir;
    /** @var string Class name only (without namespace) */
    protected $className;
    /** @var string Full class name (with namespace) */
    protected $entityClass;
    protected $routePath;
    protected $routeName;

    protected $bundleName;
    protected $bundleDir;
    protected $namespacePrefix;

    /** @var ClassNameDetails */
    protected $repositoryClassDetails;
    /** @var ClassNameDetails */
    protected $entityClassDetails;
    /** @var ClassNameDetails */
    protected $formClassDetails;
    /** @var ClassNameDetails */
    protected $controllerClassDetails;
    /** @var EntityDetails */
    protected $entityDoctrineDetails;

    protected $entityVarPlural;
    protected $entityVarSingular;
    protected $entityTwigVarPlural;
    protected $entityTwigVarSingular;

    protected $format = 'yaml'; // annotation|yaml

    protected $roles = array();

    /**
     * Symfony\Component\Security\Core\Role\RoleHierarchy::getReachableRoles(array $roles)
     */

    /**
     *
     * @param Generator      $generator
     * @param FileManager    $fileManager
     * @param DoctrineHelper $doctrineHelper
     * @param string         $projectDir
     * @param string         $skeletonDir
     */
    public function __construct(Generator $generator, FileManager $fileManager, DoctrineHelper $doctrineHelper, string $projectDir, string $skeletonDir)
    {
        $this->generator = $generator;
        $this->fileManager = $fileManager;
        $this->doctrineHelper = $doctrineHelper;
        $this->projectDir = $projectDir;
        $this->skeletonDir = $skeletonDir;

        $this->inflector = InflectorFactory::create()->build();
    }

    /**
     *
     * @param SymfonyStyle $io
     * @return $this
     */
    public function setIO(SymfonyStyle $io)
    {
        $this->io = $io;
        $this->fileManager->setIO($io);

        return $this;
    }

    /**
     *
     * @param Generator $generator
     * @return $this
     */
    public function setGenerator(Generator $generator)
    {
        $this->generator = $generator;

        return $this;
    }

    /**
     *
     * @param type $entityClass
     * @return $this
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        $ref = new \ReflectionClass($entityClass);
        $namespace = $ref->getNamespaceName();

        $this->namespacePrefix = substr($namespace, 0, strpos($namespace, '\Entity'));
        $this->className = $ref->getShortName();

        $this->bundleName = str_replace('\\', '', $this->namespacePrefix);
        $this->bundleDir = sprintf('src/%s', str_replace('\\', '/', $this->namespacePrefix));

        // generate (& fix) base route path
        $routePath = Str::asRoutePath($this->entityClass);
        $routePath = preg_replace('/(bundle|entity)\//', '', $routePath);

        // use setter to build also route name
        $this->setRoutePath($routePath);

        $this->buildClassDetails();

        return $this;
    }

    /**
     *
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     *
     * @return type
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     *
     * @param type $format
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     *
     * @return type
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     *
     * @return $this
     */
    public function setRoutePath($routePath)
    {
        $this->routePath = $routePath;

        // generate (& fix) base route name
        $this->routeName = Str::asRouteName(trim($this->routePath, '/'));

        return $this;
    }

    /**
     *
     * @return array
     */
    public function getEntitiesForAutocomplete()
    {
        return $this->doctrineHelper->getEntitiesForAutocomplete();
    }

    /**
     *
     * @return type
     */
    public function getRoutePath()
    {
        return $this->routePath;
    }

    /**
     *
     */
    protected function buildClassDetails()
    {
        // create entity class details
        $this->entityClassDetails = new ClassNameDetails($this->entityClass,  $this->namespacePrefix.'\\Entity\\', '');
        $this->entityDoctrineDetails = $this->doctrineHelper->createDoctrineDetails($this->entityClassDetails->getFullName());

	    $this->entityVarPlural = lcfirst($this->inflector->pluralize($this->entityClassDetails->getShortName()));
	    $this->entityVarSingular = lcfirst($this->inflector->singularize($this->entityClassDetails->getShortName()));

	    $this->entityTwigVarPlural = Str::asTwigVariable($this->entityVarPlural);
	    $this->entityTwigVarSingular = Str::asTwigVariable($this->entityVarSingular);

        // create form class details
        $formClass = sprintf('%s\\Form\\%sType', $this->namespacePrefix, $this->className);
        $this->formClassDetails = new ClassNameDetails($formClass, $this->namespacePrefix.'\\Form\\', 'Type');

        // create repository class details
        $repositoryClass = sprintf('%s\\Repository\\%sRepository', $this->namespacePrefix, $this->className);
        $this->repositoryClassDetails = new ClassNameDetails($repositoryClass, $this->namespacePrefix.'\\Repository\\', 'Repository');

        // create controller class details
        $controllerClass = sprintf('%s\\Controller\\%sController', $this->namespacePrefix, $this->className);
        $this->controllerClassDetails = new ClassNameDetails($controllerClass, $this->namespacePrefix.'\\Controller\\', 'Controller');
    }

    /**
     * Generate files
     *
     * @throws \Exception
     */
    public function generate()
    {
        if (!class_exists($this->entityClass)) {
            throw new \Exception('Invalid class name: '.$this->entityClass);
        }

        $this
            ->generateForm()
            ->generateController()
            ->generateTemplates()
            ->generateRoutingFiles()
        ;

        $this->generator->writeChanges();

	    $msg = "Please manually update twig configuration with path alias:<fg=green> 
twig:
    paths:
        '%%kernel.project_dir%%/%s/Resources/views' : '%s'
</>";

	    $this->io->writeln(sprintf($msg, $this->bundleDir, $this->bundleName));
    }

    /**
     *
     * @return $this
     */
    protected function generateForm()
    {
        $formFields = $this->entityDoctrineDetails->getFormFields();

        $fields = [];
        foreach ($formFields as $name => $fieldTypeOptions) {
            $fieldTypeOptions = $fieldTypeOptions ?? ['type' => null, 'options_code' => null];

            if (isset($fieldTypeOptions['type'])) {
                $fieldTypeUseStatements[] = $fieldTypeOptions['type'];
                $fieldTypeOptions['type'] = Str::getShortClassName($fieldTypeOptions['type']);
            }

            $fields[$name] = $fieldTypeOptions;
        }

        $this->generator->generateClass(
            $this->formClassDetails->getFullName(),
            $this->generateTemplatePath('form/Type.tpl.php'),
            array(
                'bounded_full_class_name' => $this->entityClassDetails->getFullName(),
                'bounded_class_name' => $this->entityClassDetails->getShortName(),
                'form_fields' => $fields,
            )
        );



        return $this;
    }

    /**
     *
     * @return $this
     */
    protected function generateController()
    {
        $templatesPath = '@'.$this->bundleName.'/'.$this->className;

        $repositoryVars = array(
            'repository_full_class_name' => $this->repositoryClassDetails->getFullName(),
            'repository_class_name' => $this->repositoryClassDetails->getShortName(),
            'repository_var' => lcfirst($this->inflector->singularize($this->repositoryClassDetails->getShortName())),
        );

        $this->generator->generateController(
            $this->controllerClassDetails->getFullName(),
            $this->generateTemplatePath('crud/controller/Controller.tpl.php'),
            array_merge([
                    'entity_full_class_name' => $this->entityClassDetails->getFullName(),
                    'entity_class_name' => $this->entityClassDetails->getShortName(),
                    'form_full_class_name' => $this->formClassDetails->getFullName(),
                    'form_class_name' => $this->formClassDetails->getShortName(),
                    'route_path' => $this->routePath,
                    'route_name' => $this->routeName,
                    'templates_path' => $templatesPath,
                    'entity_var_plural' => $this->entityVarPlural,
                    'entity_twig_var_plural' => $this->entityTwigVarPlural,
                    'entity_var_singular' => $this->entityVarSingular,
                    'entity_twig_var_singular' => $this->entityTwigVarSingular,
                    'entity_identifier' => $this->entityDoctrineDetails->getIdentifier(),
                    'format' => $this->format,
                    'roles' => $this->roles,
                ],
                $repositoryVars
            )
        );

        return $this;
    }

    /**
     *
     * @return $this
     */
    protected function generateTemplates()
    {
	    $templatesPath = '@'.$this->bundleName.'/'.$this->className;

        // generate templates
        $templates = [
            '_delete_form' => [
                'route_name' => $this->routeName,
                'entity_twig_var_singular' => $this->entityTwigVarSingular,
                'entity_identifier' => $this->entityDoctrineDetails->getIdentifier(),
                'bundle_name' => $this->bundleName,
            ],
            '_form' => [],
            'edit' => [
                'entity_class_name' => $this->entityClassDetails->getShortName(),
                'entity_twig_var_singular' => $this->entityTwigVarSingular,
                'entity_identifier' => $this->entityDoctrineDetails->getIdentifier(),
                'bundle_name' => $this->bundleName,
                'route_name' => $this->routeName,
	            'templates_path' => $templatesPath,
            ],
            'index' => [
                'entity_class_name' => $this->entityClassDetails->getShortName(),
                'entity_twig_var_plural' => $this->entityTwigVarPlural,
                'entity_twig_var_singular' => $this->entityTwigVarSingular,
                'entity_identifier' => $this->entityDoctrineDetails->getIdentifier(),
                'entity_fields' => $this->entityDoctrineDetails->getDisplayFields(),
                'bundle_name' => $this->bundleName,
                'route_name' => $this->routeName,
	            'templates_path' => $templatesPath,
            ],
            'new' => [
                'entity_class_name' => $this->entityClassDetails->getShortName(),
                'bundle_name' => $this->bundleName,
                'route_name' => $this->routeName,
	            'templates_path' => $templatesPath,
            ],
            'show' => [
                'entity_class_name' => $this->entityClassDetails->getShortName(),
                'entity_twig_var_singular' => $this->entityTwigVarSingular,
                'entity_identifier' => $this->entityDoctrineDetails->getIdentifier(),
                'entity_fields' => $this->entityDoctrineDetails->getDisplayFields(),
                'bundle_name' => $this->bundleName,
                'route_name' => $this->routeName,
	            'templates_path' => $templatesPath,
            ],
        ];

        foreach ($templates as $template => $variables) {
            $filePath = sprintf('%s/Resources/views/%s/%s.html.twig', $this->bundleDir, $this->className, $template);
            $templatePath = $this->generateTemplatePath('crud/templates/'.$template.'.tpl.php');

            $this->generator->generateFile($filePath, $templatePath, $variables);
        }

        return $this;
    }

    /**
     *
     * @return $this
     */
    protected function generateRoutingFiles()
    {
        if ($this->format !== 'annotation') {
            // generate crud routing file
            $crudRoutingFilePath = sprintf('%s/Resources/config/routing/%s.%s', $this->bundleDir, $this->entityTwigVarSingular, $this->format);
            $crudRoutingTemplatePath = $this->generateTemplatePath(sprintf('crud/config/routing-crud.%s.tpl.php', $this->format));

            $crudRoutingVars = array(
                'bundle_name' => $this->bundleName,
                'controller_full_class_name' => $this->controllerClassDetails->getFullName(),
                'entity' => $this->className,
                'route_name' => $this->routeName,
                'entity_identifier' => $this->entityDoctrineDetails->getIdentifier(),
                'format' => $this->format,
            );

            $this->generator->generateFile($crudRoutingFilePath, $crudRoutingTemplatePath, $crudRoutingVars);

            // generate or append to include file
            $routingFilePath = sprintf('%s/Resources/config/routing.%s', $this->bundleDir, $this->format);
            $routingTemplatePath = $this->generateTemplatePath(sprintf('crud/config/routing.%s.tpl.php', $this->format));

            $routeNameInclude = sprintf('%s_%s', $this->routeName, $this->routeName);

            $routingVars = array(
                'bundle_name' => $this->bundleName,
                'route_name' => $this->routeName,
                'route_path' => $this->routePath,
                'route_name_include' => $routeNameInclude,
                'entity_twig_var_singular' => $this->entityTwigVarSingular,
                'format' => $this->format,
            );

            // file exists... append
            if (!file_exists($routingFilePath)) {
                $this->generator->generateFile($routingFilePath, $routingTemplatePath, $routingVars);
            }
            else {
                $existingContent = file_get_contents($routingFilePath);

                // prepend route include to file
                if (strpos($existingContent, $routeNameInclude) === FALSE) {
                    $parsedContent = $this->fileManager->parseTemplate($routingTemplatePath, $routingVars);

                    $newContent = $parsedContent."\r\n\r\n".$existingContent;

                    $this->fileManager->dumpFile($routingFilePath, $newContent);
                }
            }
        }

        return $this;
    }

    /**
     * Generate template file path
     *
     * @param string $templateName
     * @return type
     */
    protected function generateTemplatePath($templateName)
    {
        return $this->skeletonDir.'/'.$templateName;
    }
}
