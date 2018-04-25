<?php

namespace ic\Framework\Plugin;

use ic\Framework\Debug\Debug;
use ic\Framework\Hook\HookDecorator;

/**
 * Class PluginAccessDecorator
 *
 * @package ic\Framework\Plugin
 */
trait PluginClassDecorator
{

    use HookDecorator;
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
     * @return Plugin;
     */
    protected function setPlugin(PluginBase $plugin)
    {
        if (!$this->plugin) {
            $this->plugin = $plugin;

            $this->setMetadata($plugin);
            $this->setOptions($plugin->getOptions());
        }

        return $this->plugin;
    }

    /**
     * @return Plugin|null
     */
    public function getPlugin()
    {
        if ($this->plugin === null) {
            Debug::error('There is no Plugin object attached.', static::class);

            return null;
        }

        return $this->plugin;
    }

    /**
     * @param string $pathName
     *
     * @return string
     */
    public function getRelativePath($pathName = '')
    {
        return $this->plugin->getRelativePath($pathName);
    }

    /**
     * @param string $pathName
     *
     * @return string
     */
    public function getAbsolutePath($pathName = '')
    {
        return $this->plugin->getAbsolutePath($pathName);
    }

}