<?php

namespace Gesof\MakerBundle\Helper;

use Gesof\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
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
	 * @throws \Exception
	 */
	public function generate()
	{
//		$bundleFilePath = sprintf('%s/%s/%s.php', $this->bundleDetails->getOutputDirectory(), $this->bundleDetails->getNamespace(), $this->bundleDetails->getName());
//		$bundleFilePath = preg_replace('/(\\\\)+/', DIRECTORY_SEPARATOR, $bundleFilePath);
//
//		echo "generating $bundleFilePath \n";

		$this->generateBundleClass();
		$this->generateConfigurationClass();
		$this->generateExtensionClass();

		$this->generator->writeChanges();
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
}