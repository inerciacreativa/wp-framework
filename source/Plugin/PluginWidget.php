<?php

namespace ic\Framework\Plugin;

use ic\Framework\Widget\Widget;

/**
 * Class Widget
 *
 * @package ic\Framework\Plugin
 */
abstract class PluginWidget extends Widget
{

	/**
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * @param Plugin $plugin
	 *
	 * @return static
	 */
	public static function create(Plugin $plugin)
	{
		return new static($plugin);
	}

	/**
	 * Widget constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct(Plugin $plugin)
	{
		$this->plugin = $plugin;

		parent::__construct();
	}

	/**
	 * @inheritdoc
	 */
	public function id(): string
	{
		return $this->plugin->id();
	}

	/**
	 * @inheritdoc
	 */
	public function name(): string
	{
		return $this->plugin->name();
	}

}
