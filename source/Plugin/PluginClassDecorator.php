<?php

namespace ic\Framework\Plugin;

use ic\Framework\Hook\Hookable;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class PluginClassDecorator
 *
 * @package ic\Framework\Plugin
 */
trait PluginClassDecorator
{

	use Hookable;
	use MetadataDecorator;
	use OptionsDecorator;
	use AssetsDecorator;

	/**
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * @param PluginBase $plugin
	 *
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 */
	protected function setPlugin(PluginBase $plugin): void
	{
		if ($this->plugin) {
			throw new RuntimeException('There is already a Plugin object attached.');
		}

		$this->plugin = $plugin;

		$this->setMetadata($plugin->getMetadata());
		$this->setOptions($plugin->getOptions());
	}

	/**
	 * @return Plugin|PluginClass
	 *
	 * @throws RuntimeException
	 */
	public function getPlugin()
	{
		if ($this->plugin === null) {
			throw new RuntimeException('There is no Plugin object attached.');
		}

		return $this->plugin;
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 *
	 * @throws RuntimeException
	 */
	public function getRelativePath(string $path = ''): string
	{
		return $this->getPlugin()->getRelativePath($path);
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 *
	 * @throws RuntimeException
	 */
	public function getAbsolutePath($path = ''): string
	{
		return $this->getPlugin()->getAbsolutePath($path);
	}

}