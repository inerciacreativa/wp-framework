<?php

namespace ic\Framework\Plugin;

use ic\Framework\Debug\Debug;
use ic\Framework\Support\Options;

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
    protected function setOptions($options, $network = 0)
    {
        if ($options instanceof Options) {
            $this->options = $options;
        } elseif (($this instanceof PluginBase) && \is_array($options)) {
            $this->options = new Options($this->id, $options, $network);
        }

        return $this;
    }

    /**
     * @return Options
     */
    public function getOptions(): Options
    {
        if (!$this->options) {
            //throw new \RuntimeException('There is not Options object attached.');
            Debug::error(sprintf('There is not options object attached to "%s".', static::class));

            $this->setOptions([]);
        }

        return $this->options;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return $this->getOptions()->get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return static
     */
    public function setOption($key, $value)
    {
        $this->getOptions()->set($key, $value);

        return $this;
    }

}