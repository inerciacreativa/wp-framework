<?php

namespace ic\Framework\Plugin;

/**
 * Class PluginBackend
 *
 * @package ic\Framework\Plugin
 */
abstract class PluginBackend
{

    use PluginDecorator;

    /**
     * PluginBackend constructor.
     *
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin($plugin);

        $this->hook()->on('init', function () {
            $this->register();
        });

        $this->initialize();
    }

    /**
     *
     */
    protected function initialize()
    {
    }

    /**
     *
     */
    protected function register()
    {
    }

}