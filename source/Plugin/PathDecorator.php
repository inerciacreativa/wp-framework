<?php

namespace ic\Framework\Plugin;

use ic\Framework\Support\Arr;

/**
 * Class PathDecorator
 *
 * @package ic\Framework\Support
 */
trait PathDecorator
{

	/**
	 * @var array
	 */
	private $paths = [];

	/**
	 * @param string $fileName
	 * @param string $rootName
	 *
	 * @return $this
	 */
	protected function setPaths(string $fileName, string $rootName): self
	{
		$baseName = basename(dirname($fileName));
		$rootName = wp_normalize_path($rootName . '/' . $baseName);
		$fileName = basename($fileName);

		$this->paths = compact('baseName', 'rootName', 'fileName');

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFileName(): string
	{
		return $this->getAbsolutePath(Arr::get($this->paths, 'fileName', ''));
	}

	/**
	 * @return string
	 */
	public function getRootName(): string
	{
		return Arr::get($this->paths, 'rootName', '');
	}

	/**
	 * @return string
	 */
	public function getBaseName(): string
	{
		return Arr::get($this->paths, 'baseName', '');
	}

	/**
	 * Return the absolute path to the plugin.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function getAbsolutePath(string $path = ''): string
	{
		return $this->getRootName() . $this->getPath($path);
	}

	/**
	 * Return the relative path to the plugin.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function getRelativePath(string $path = ''): string
	{
		return $this->getBaseName() . $this->getPath($path);
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	protected function getPath(string $path): string
	{
		return $path ? ('/' . ltrim(wp_normalize_path($path), '/\\')) : '';
	}

}
