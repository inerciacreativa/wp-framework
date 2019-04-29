<?php

namespace ic\Framework\Plugin;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class PluginBackend
 *
 * @package ic\Framework\Plugin
 */
abstract class PluginClass
{

	use PluginClassDecorator;

	/**
	 * PluginBackend constructor.
	 *
	 * @param PluginBase $plugin
	 *
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 */
	public function __construct(PluginBase $plugin)
	{
		$this->setPlugin($plugin);

		$this->configure();
	}

	/**
	 *
	 */
	protected function configure(): void
	{
		$this->hook()->on('init', 'initialize');
	}

	/**
	 *
	 */
	protected function initialize(): void
	{
	}

}