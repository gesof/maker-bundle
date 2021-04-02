<?php

namespace Gesof\MakerBundle\Helper;

use Symfony\Component\DependencyInjection\Container;

class BundleDetails
{
	protected $namespace;
	protected $name;
	protected $outputDirectory;
	protected $format;

	public function __construct()
	{
	}

	/**
	 * @return mixed
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * @param mixed $namespace
	 * @return BundleDetails
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param mixed $name
	 * @return BundleDetails
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return false|string
	 */
	public function getBasename()
	{
		return substr($this->name, 0, -6);
	}

	/**
	 * @return string
	 */
	public function getExtensionAlias()
	{
		return Container::underscore($this->getBasename());
	}

	/**
	 * @return mixed
	 */
	public function getOutputDirectory() {
		return $this->outputDirectory;
	}

	/**
	 * @param mixed $outputDirectory
	 * @return BundleDetails
	 */
	public function setOutputDirectory($outputDirectory)
	{
		$this->outputDirectory = $outputDirectory;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getFormat()
	{
		return $this->format;
	}

	/**
	 * @param mixed $format
	 * @return BundleDetails
	 */
	public function setFormat($format)
	{
		$this->format = $format;

		return $this;
	}
}