<?php

namespace ic\Framework\Plugin;

use ic\Framework\Hook\Hookable;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

/**
 * Class PluginBase
 *
 * @package ic\Framework\Plugin
 */
abstract class PluginBase
{

	use PathDecorator;
	use Hookable;
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
	final public static function instance()
	{
		return self::create('');
	}

	/**
	 * @param string $filename
	 * @param string $root
	 *
	 * @return static
	 */
	final public static function create(string $filename, string $root = WP_PLUGIN_DIR)
	{
		$class = static::class;

		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class($filename, $root);
		}

		return self::$instances[$class];
	}

	/**
	 * @param string $filename
	 * @param string $root
	 *
	 * @throws RuntimeException
	 * @throws ReflectionException
	 */
	final private function __construct($filename, $root = WP_PLUGIN_DIR)
	{
		$this->setPaths($filename, $root);
		$this->setMetadata($this->getFileName());
		$this->setAssets();

		$this->configure();

		$namespace = (new ReflectionClass($this))->getNamespaceName();
		$class     = $namespace . '\\' . (is_admin() ? 'Backend' : 'Frontend');

		if (class_exists($class)) {
			new $class($this);
		}
	}

	/**
	 *
	 */
	final private function __clone()
	{
	}

	/**
	 * @throws RuntimeException
	 */
	final public function __wakeup()
	{
		throw new RuntimeException('Cannot unserialize singleton.');
	}

	/**
	 * Runs once the plugin is configured.
	 */
	protected function configure(): void
	{
		$this->hook()
		     ->before('init', 'initialize')
		     ->on('plugins_loaded', 'translation');
	}

	/**
	 * Used to register custom post types, taxonomies and widgets.
	 */
	protected function initialize(): void
	{
	}

	/**
	 *
	 */
	protected function translation(): void
	{
		load_plugin_textdomain($this->id(), false, $this->getRelativePath($this->languages()));
	}

}