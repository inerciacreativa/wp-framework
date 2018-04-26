<?php

namespace ic\Framework\Plugin;

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
     */
    public function __construct(PluginBase $plugin)
    {
        $this->setPlugin($plugin);

        $this->setHook()->on('init', 'onInit');

        $this->onCreation();
    }

    /**
     *
     */
    protected function onCreation()
    {
    }

    /**
     *
     */
    protected function onInit()
    {
    }

}