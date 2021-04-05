<?php

namespace Gesof\MakerBundle\Helper;

use Gesof\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Component\Console\Style\SymfonyStyle;

class BundleHelper
{
	/** @var Generator */
	protected $generator;
	/** @var FileManager */
	protected $fileManager;
	/** @var SymfonyStyle */
	protected $io;
	protected $skeletonDir;
	protected $projectDir;
	/** @var BundleDetails */
	protected $bundleDetails;
	/** @var ClassNameDetails */
	protected $controllerClassDetails;

	/**
	 * BundleHelper constructor.
	 * @param Generator $generator
	 * @param FileManager $fileManager
	 * @param $projectDir
	 * @param $skeletonDir
	 */
	public function __construct(Generator $generator, FileManager $fileManager, $projectDir, $skeletonDir)
	{
		$this->generator = $generator;
		$this->fileManager = $fileManager;
		$this->projectDir = $projectDir;
		$this->skeletonDir = $skeletonDir;
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

	public function setBundleDetails(BundleDetails $bundleDetails)
	{
		$this->bundleDetails = $bundleDetails;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getProjectDir()
	{
		return $this->projectDir;
	}

	/**
	 * @throws \Exception
	 */
	public function generate()
	{
		$controllerMinName = 'Default';
		$controllerClass = sprintf('%s\\Controller\\%sController', $this->bundleDetails->getNamespace(), $controllerMinName);
		$this->controllerClassDetails = new ClassNameDetails($controllerClass, $this->bundleDetails->getNamespace().'\\Controller\\', 'Controller');

		$this->generateBundleClass();
		$this->generateConfigurationClass();
		$this->generateExtensionClass();
		$this->generateServicesConfiguration();
		$this->generateRoutingComponents();
		$this->enableBundle();

		$this->generator->writeChanges();

		$msg = "Please manually update twig configuration with path alias:<fg=green> 
twig:
    paths:
        '%%kernel.project_dir%%/%s/Resources/views' : '%s'
</>";

		$this->io->writeln(sprintf($msg, $this->bundleDetails->getDirectory(), $this->bundleDetails->getName()));
	}

	/**
	 * @throws \Exception
	 */
	protected function generateBundleClass()
	{
		$bundleClass = $this->bundleDetails->getNamespace() . '\\' . $this->bundleDetails->getName();

		$templateName = $this->skeletonDir.'/bundle/Bundle.tpl.php';
		$this->generator->generateClass($bundleClass, $templateName, array(
			'bundle_name' => $this->bundleDetails->getName()
		));
	}

	/**
	 * @throws \Exception
	 */
	protected function generateConfigurationClass()
	{
		$bundleClass = sprintf('%s\\DependencyInjection\\Configuration', $this->bundleDetails->getNamespace());

		$templateName = $this->skeletonDir.'/bundle/Configuration.tpl.php';
		$this->generator->generateClass($bundleClass, $templateName, array(
			'extension_alias' => $this->bundleDetails->getExtensionAlias()
		));
	}

	/**
	 * @throws \Exception
	 */
	protected function generateExtensionClass()
	{
		$bundleClass = sprintf('%s\\DependencyInjection\\%sExtension', $this->bundleDetails->getNamespace(), $this->bundleDetails->getBasename());

		$templateName = $this->skeletonDir.'/bundle/Extension.tpl.php';
		$this->generator->generateClass($bundleClass, $templateName, array(
			'class_name' => $this->bundleDetails->getBasename(),
			'format' => $this->bundleDetails->getFormat(),
		));
	}

	/**
	 * @throws \Exception
	 */
	protected function generateServicesConfiguration()
	{
		$servicesFilePath = sprintf('%s/Resources/config/services.%s', $this->bundleDetails->getDirectory(), $this->bundleDetails->getFormat());
		$templatePath = sprintf('%s/bundle/services.%s.tpl.php', $this->skeletonDir, $this->bundleDetails->getFormat());

		$this->generator->generateFile($servicesFilePath, $templatePath, array(
			'namespace' => $this->bundleDetails->getNamespace(),
			'extension_alias' => $this->bundleDetails->getExtensionAlias(),
			'controller_full_class_name' => $this->controllerClassDetails->getFullName(),
		));
	}

	/**
	 *
	 */
	protected function generateRoutingComponents()
	{
		$routePath = $this->bundleDetails->getExtensionAlias();

		// create controller class details
		$controllerTemplatePath = sprintf('%s/bundle/Controller.tpl.php', $this->skeletonDir);
		$controllerMinName = substr($this->controllerClassDetails->getShortName(), 0, strpos($this->controllerClassDetails->getShortName(), 'Controller'));

		$this->generator->generateClass($this->controllerClassDetails->getFullName(), $controllerTemplatePath, array(
			'route_path' => $routePath,
			'parent_class_name' => 'AbstractController',
			'format' => $this->bundleDetails->getFormat(),
			'templates_path' => sprintf('@%s/%s', $this->bundleDetails->getName(), $controllerMinName),
		));

		// create TWIG file
		$twigFilePath = sprintf('%s/Resources/views/%s/index.html.twig', $this->bundleDetails->getDirectory(), $controllerMinName);
		$templatePath = sprintf('%s/bundle/index.html.twig.tpl.php', $this->skeletonDir);

		$this->generator->generateFile($twigFilePath, $templatePath, array(

		));

		// create routing file
		$routingFilePath = sprintf('%s/Resources/config/routing.%s', $this->bundleDetails->getDirectory(), $this->bundleDetails->getFormat());
		$templatePath = sprintf('%s/bundle/routing.%s.tpl.php', $this->skeletonDir, $this->bundleDetails->getFormat());

		$this->generator->generateFile($routingFilePath, $templatePath, array(
			'namespace' => $this->bundleDetails->getNamespace(),
			'extension_alias' => $this->bundleDetails->getExtensionAlias(),
			'controller_full_class_name' => $this->controllerClassDetails->getFullName(),
			'route_path' => $routePath,
			'route_prefix' => str_replace('_', '', $routePath)
		));

		// create routing include file
		$routingIncludeFilePath = sprintf('%s/routes/%s.yaml', $this->getConfigDir(), $this->bundleDetails->getExtensionAlias());
		$templatePath = sprintf('%s/bundle/routing_resource.%s.tpl.php', $this->skeletonDir, $this->bundleDetails->getFormat());

		$this->generator->generateFile($routingIncludeFilePath, $templatePath, array(
			'bundle_name' => $this->bundleDetails->getName(),
			'namespace' => $this->bundleDetails->getNamespace(),
			'route_path' => $routePath,
		));
	}

	/**
	 *
	 */
	protected function enableBundle()
	{
		$filePath = $this->getConfigDir().'/bundles.php';
		$bundles = require $filePath;

		$bundleClass = $this->bundleDetails->getNamespace() . '\\' . $this->bundleDetails->getName();

		$bundles[$bundleClass] = array(
			'all' => true
		);

		$this->dumpBundlesFile($bundles, $filePath);
	}

	/**
	 * @param array $bundles
	 * @param string $filePath
	 */
	protected function dumpBundlesFile(array $bundles, string $filePath)
	{
		$contents = "<?php\n\nreturn [\n";

		foreach ($bundles as $class => $envs) {
			$contents .= "    $class::class => [";
			foreach ($envs as $env => $value) {
				$booleanValue = var_export($value, true);
				$contents .= "'$env' => $booleanValue, ";
			}
			$contents = substr($contents, 0, -2)."],\n";
		}
		$contents .= "];\n";

		if (!is_dir(\dirname($filePath))) {
			mkdir(\dirname($filePath), 0777, true);
		}

		$this->generator->dumpFile($filePath, $contents);

		if (\function_exists('opcache_invalidate')) {
			opcache_invalidate($filePath);
		}
	}

	/**
	 * @return string
	 */
	public function getConfigDir()
	{
		return $this->projectDir . '/config';
	}
}