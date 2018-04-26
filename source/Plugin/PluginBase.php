<?php

namespace ic\Framework\Plugin;

use ic\Framework\Debug\Debug;
use ic\Framework\Hook\HookDecorator;
use ic\Framework\Support\PathDecorator;

/**
 * Class PluginBase
 *
 * @package ic\Framework\Plugin
 */
abstract class PluginBase
{

    use PathDecorator;
    use HookDecorator;
    use MetadataDecorator;
    use OptionsDecorator;
    use AssetsDecorator;

    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @return static
     */
    public static function getInstance()
    {
        return self::create();
    }

    /**
     * @return static
     */
    public static function create()
    {
        $class = get_called_class();

        if (!isset(self::$instances[$class])) {
            $reflection  = new \ReflectionClass($class);
            $instance    = $reflection->newInstanceWithoutConstructor();
            $constructor = $reflection->getConstructor();
            $constructor->setAccessible(true);
            $constructor->invokeArgs($instance, func_get_args());

            self::$instances[$class] = $instance;
        }

        return self::$instances[$class];
    }

    /**
     * @param string $fileName
     * @param string $rootName
     */
    final protected function __construct($fileName, $rootName = WP_PLUGIN_DIR)
    {
        $this->setPaths($fileName, $rootName);
        $this->setMetadata($this->getFileName());
        $this->setAssets();

        $this->onCreation();

        $nameSpace = (new \ReflectionClass($this))->getNamespaceName();
        $className = $nameSpace . '\\' . (is_admin() ? 'Backend' : 'Frontend');

        if (class_exists($className)) {
            new $className($this);
        } elseif (is_admin()) {
            $this->onBackend();
        } else {
            $this->onFrontend();
        }
    }

    /**
     *
     */
    final protected function __clone()
    {
    }

    /**
     * @throws \RuntimeException
     */
    final public function __wakeup()
    {
        throw new \RuntimeException('Cannot unserialize singleton.');
    }

    /**
     * Runs once the plugin is configured.
     */
    protected function onCreation()
    {
        $this->setHook()->before('init', function () {
            $this->setTranslation();
            $this->onInit();
        });
    }

    /**
     * Used to register custom post types, taxonomies and widgets.
     */
    protected function onInit()
    {
    }

    /**
     *
     */
    protected function onBackend()
    {
    }

    /**
     *
     */
    protected function onFrontend()
    {
    }

    /**
     *
     */
    protected function setTranslation()
    {
    }

}