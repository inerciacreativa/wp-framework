<?php

namespace ic\Framework\Plugin;

use ic\Framework\Hook\HookDecorator;

/**
 * Class PluginDecorator
 *
 * @package ic\Framework\Plugin
 */
trait PluginDecorator
{

    use HookDecorator;

    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @param Plugin|null $plugin
     *
     * @return Plugin;
     */
    protected function plugin(Plugin $plugin = null)
    {
        if (!$this->plugin) {
            if ($plugin) {
                $this->plugin = $plugin;
            } else {
                throw new \RuntimeException('There is no Plugin object attached.');
            }
        }

        return $this->plugin;
    }

    /**
     * Return the plugin id.
     *
     * @return string
     */
    protected function id()
    {
        return $this->plugin()->id();
    }

    /**
     * @return string
     */
    protected function name()
    {
        return $this->plugin()->name();
    }

    /**
     * @return string
     */
    protected function version()
    {
        return $this->plugin()->version();
    }

    /**
     * @return PluginOptions|null
     */
    protected function options()
    {
        return $this->plugin()->options();
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function get($key)
    {
        return $this->options()->get($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return PluginOptions
     */
    protected function set($key, $value)
    {
        return $this->options()->set($key, $value);
    }

    protected function register()
    {
    }

}