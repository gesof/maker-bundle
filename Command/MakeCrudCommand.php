<?php

namespace Gesof\MakerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;

use Gesof\MakerBundle\Helper\CrudHelper;

/**
 * 
 */
class MakeCrudCommand extends Command
{
    protected $crudHelper;
    protected $availableRoles = array();
    protected $io;
    
    /**
     * 
     * @param CrudHelper $crudHelper
     * @param array $roleHierarchy
     */
    public function __construct(CrudHelper $crudHelper, $roleHierarchy)
    {
        $this->crudHelper = $crudHelper;
        $this->availableRoles = $this->extractAvailableRoles($roleHierarchy);

        parent::__construct();
    }
            
    /**
     * 
     */
    protected function configure()
    {
        $this
            ->setName('gesof:make:crud')
            ->setDescription('Creates CRUD for Doctrine entity class')
            ->addArgument('entity-class', InputArgument::OPTIONAL, sprintf('The entity class to create CRUD (e.g. <fg=yellow>Acme\DemoBundle\Entity\%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'The format used for routing (yaml or annotation)')
            ->addOption('roles', null,InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Security roles to apply', array())
            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakeCrud.txt'))
        ;
    }

    /**
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function interact(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        
        if (null === $input->getArgument('entity-class')) {
            $argument = $this->getDefinition()->getArgument('entity-class');

            $entities = $this->crudHelper->getEntitiesForAutocomplete();
            sort($entities);
            
            $question = new Question($argument->getDescription());
            $question->setAutocompleterValues($entities);

            $question->setValidator(function($value) use ($entities) {
                return Validator::entityExists($value, $entities);
            });
            
            $value = $this->io->askQuestion($question);

            $input->setArgument('entity-class', $value);
        }
        
        if (empty($input->getOption('roles'))) {
            $question = new ConfirmationQuestion('Do you want to add security roles?', TRUE,'/^(y)/i');

            if ($this->io->askQuestion($question)) {
                $option = $this->getDefinition()->getOption('roles');
                $question = new ChoiceQuestion($option->getDescription(), $this->availableRoles, NULL);
                $question->setMultiselect(true);

                $roles = $this->io->askQuestion($question);
                $input->setOption('roles', $roles);
            }
        }
        
        if (null === $input->getOption('format')) {
            $option = $this->getDefinition()->getOption('format');
            
            $question = new Question($option->getDescription(), 'yaml');
            $question->setAutocompleterValues(array('yaml', 'annotation'));

            $value = $this->io->askQuestion($question);

            $input->setOption('format', $value);
        }
    }
    
    /**
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityClass = $input->getArgument('entity-class');
        $format = $input->getOption('format');
        $roles = $input->getOption('roles');
        
        $this->crudHelper
            ->setIO($this->io)
            ->setEntityClass($entityClass)
            ->setRoles($roles)
            ->setFormat($format)
        ;
        
        
        $question = new Question('Enter route path prefix', $this->crudHelper->getRoutePath());
        $routePath = $this->io->askQuestion($question);
        
        $this->crudHelper->setRoutePath($routePath);
        
        $confirmQuestion = new ConfirmationQuestion('Generate?', TRUE,'/^(y)/i');
        
        if ($this->io->askQuestion($confirmQuestion)) {
            $this->crudHelper->generate();
            
            $this->io->success('Operation successful!');
        }
    }
    
    /**
     * Extract unique roles from role hierarchy
     * 
     * @param type $roleHierarchy
     * @return array
     */
    protected function extractAvailableRoles($roleHierarchy)
    {
        // always add this roles first
        $availableRoles = array(
            'ROLE_USER',
            'ROLE_ADMIN'
        );
        
        array_walk_recursive($roleHierarchy, function($role) use (&$availableRoles) {
            if (array_search($role, $availableRoles) === FALSE) {
                $availableRoles[] = $role;
            }
        });
        
        return $availableRoles;
    }
}