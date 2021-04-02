<?php

namespace Gesof\MakerBundle\Command;

use Gesof\MakerBundle\Helper\BundleDetails;
use Gesof\MakerBundle\Helper\BundleHelper;
use Gesof\MakerBundle\Helper\CrudHelper;
use Gesof\MakerBundle\Helper\ExtraValidator;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeBundleCommand extends Command
{
	/** @var BundleHelper */
	protected $bundleHelper;

	/**
	 *
	 * @param BundleHelper $bundleHelper
	 */
	public function __construct(BundleHelper $bundleHelper)
	{
		$this->bundleHelper = $bundleHelper;

		parent::__construct();
	}

	protected function configure()
	{
		$term = Str::asClassName(Str::getRandomTerm());

		$this
			->setName('gesof:make:bundle')
			->setDescription('Creates Symfony bundle')
			->addArgument('namespace', InputArgument::OPTIONAL, sprintf('The bundle namespace (e.g. <fg=yellow>Acme\%sBundle</>)', $term))
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

		if (null === $input->getArgument('namespace')) {
			$argument = $this->getDefinition()->getArgument('namespace');

			$question = new Question($argument->getDescription(), 'Acme\DemoBundle');

			$question->setValidator(function ($value) {
				Validator::notBlank($value);
				ExtraValidator::validateBundleNamespace($value);

				return $value;
			});

			$value = $this->io->askQuestion($question);

			$input->setArgument('namespace', $value);
		}
	}

	/**
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$namespace = $input->getArgument('namespace');
		$bundleName = str_replace('\\Bundle\\', '\\', $namespace);
		$bundleName = str_replace('\\', '', $bundleName);
		$dir = 'src';

		$details = new BundleDetails();
		$details
			->setName($bundleName)
			->setNamespace($namespace)
			->setOutputDirectory($dir)
			->setFormat('yaml') // only this is supported for now
		;

		$output->writeln('Creating: ' . $namespace);
		$output->writeln('Bundle: ' . $bundleName);

		$this->bundleHelper
			->setIO($this->io)
			->setBundleDetails($details)
		;

		$this->bundleHelper->generate();
		return Command::SUCCESS;
	}
}