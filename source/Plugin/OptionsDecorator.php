<?php

namespace ic\Framework\Plugin;

use ic\Framework\Data\Options;

/**
 * Trait OptionsDecorator
 *
 * @package ic\Framework\Plugin
 */
trait OptionsDecorator
{

	/**
	 * @var Options
	 */
	private $options;

	/**
	 * Use to configure the plugin options.
	 *
	 * @param array|Options $options
	 * @param int           $network
	 *
	 * @return static
	 */
	protected function setOptions($options, int $network = 0)
	{
		if ($options instanceof Options) {
			$this->options = $options;
		} else if (($this instanceof PluginBase) && is_array($options)) {
			$this->options = new Options($options, $this->id(), $network);
		}

		return $this;
	}

	/**
	 * @return Options
	 */
	public function getOptions(): ?Options
	{
		return $this->options;
	}

	/**
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function getOption(string $key, $default = null)
	{
		if ($this->getOptions()) {
			return $this->getOptions()->get($key, $default);
		}

		return $default;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return static
	 */
	public function setOption(string $key, $value)
	{
		if ($this->getOptions()) {
			$this->getOptions()->set($key, $value);
		}

		return $this;
	}

}