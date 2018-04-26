<?php

namespace ic\Framework\Plugin;

/**
 * Class PluginFrontend
 *
 * @package ic\Framework\Plugin
 */
abstract class PluginFrontend
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

        $this->initialize();
    }

    /**
     *
     */
    protected function initialize()
    {
    }

}