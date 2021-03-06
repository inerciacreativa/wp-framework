<?php

namespace ic\Framework\Plugin;

use ic\Framework\Hook\Hookable;

/**
 * Class Assets
 *
 * @package ic\Framework\Plugin
 */
class Assets
{

	use Hookable;

	public const FRONT = 1;

	public const LOGIN = 2;

	public const BACK = 4;

	public const ALL = 7;

	public const BASE = 'base';

	public const STYLE = 'css';

	public const SCRIPT = 'js';

	/**
	 * @var array
	 */
	protected static $defaults = [
		'id'      => null,
		'url'     => false,
		'type'    => null,
		'footer'  => false,
		'version' => false,
		'depends' => [],
		'on'      => true,
		'hooks'   => [],
		'path'    => '',
	];

	/**
	 * @var array
	 */
	protected static $targets = [
		self::FRONT => 'frontend',
		self::LOGIN => 'login',
		self::BACK  => 'backend',
		self::ALL   => 'everywhere',
	];

	/**
	 * @var array
	 */
	protected static $folders = [
		self::BASE   => 'public',
		self::STYLE  => 'css',
		self::SCRIPT => 'js',
	];

	/**
	 * @var array
	 */
	protected $paths = [];

	/**
	 * @var array
	 */
	protected $assets = [];

	/**
	 * @param string $path
	 *
	 * @return static
	 */
	public static function create($path = ''): Assets
	{
		return new static($path);
	}

	/**
	 * Assets constructor.
	 *
	 * @param string $path
	 */
	public function __construct($path = '')
	{
		$this->setPaths($path);

		if (is_admin()) {
			$this->hook()->before('admin_enqueue_scripts', 'enqueueBackend');
		} else {
			$this->hook()
			     ->before('wp_enqueue_scripts', 'enqueueFrontend')
			     ->before('wp_print_footer_scripts', 'enqueueFrontend')
			     ->before('login_enqueue_scripts', 'enqueueLogin');
		}
	}

	/**
	 * @param string $path
	 * @param array  $folders
	 *
	 * @return $this
	 */
	public function setPaths(string $path, array $folders = []): self
	{
		$folders = array_merge(self::$folders, $folders, ['path' => $path]);

		$folders = array_map(static function ($folder) {
			$folder = trim($folder, '/\\');

			return empty($folder) ? '' : "$folder/";
		}, $folders);

		$this->paths = [
			self::STYLE  => $folders['path'] . $folders[self::BASE] . $folders[self::STYLE],
			self::SCRIPT => $folders['path'] . $folders[self::BASE] . $folders[self::SCRIPT],
		];

		return $this;
	}

	/**
	 * @param string $source
	 * @param int    $target
	 * @param array  $parameters
	 *
	 * @return $this
	 */
	public function addStyle(string $source, int $target, array $parameters = []): self
	{
		return $this->add($source, $target, array_merge($parameters, [
			'type'   => self::STYLE,
			'footer' => false,
		]));
	}

	/**
	 * @param string $source
	 * @param int    $target
	 * @param array  $parameters
	 *
	 * @return $this
	 */
	public function addScript(string $source, int $target, array $parameters = []): self
	{
		return $this->add($source, $target, array_merge(['footer' => true], $parameters, ['type' => self::SCRIPT]));
	}

	/**
	 * @param string $source
	 * @param int    $target
	 * @param array  $parameters
	 *
	 * @return $this
	 */
	protected function add(string $source, int $target, array $parameters = []): self
	{
		$asset = (object) array_merge(self::$defaults, $parameters);

		if (empty($asset->type)) {
			$asset->type = empty($asset->url) ? $this->getType($source, false) : $this->getType($source, true);
		}

		if (!array_key_exists($target, self::$targets)) {
			$target = self::ALL;
		}

		$asset->target = $target;
		$asset->url    = empty($asset->url) ? $this->getUrl($source, $asset->type, $asset->path) : $source;
		$asset->id     = empty($asset->id) ? $this->getId($asset->url) : $asset->id;

		$this->assets[$asset->id] = $asset;

		return $this;
	}

	/**
	 *
	 */
	protected function enqueueFrontend(): void
	{
		foreach ($this->filter(self::FRONT) as $asset) {
			$this->enqueue($asset);
		}
	}

	/**
	 *
	 */
	protected function enqueueLogin(): void
	{
		foreach ($this->filter(self::LOGIN) as $asset) {
			$this->enqueue($asset);
		}
	}

	/**
	 * @param string $hook
	 */
	protected function enqueueBackend($hook): void
	{
		foreach ($this->filter(self::BACK, $hook) as $asset) {
			$this->enqueue($asset);
		}
	}

	/**
	 * @param object $asset
	 */
	protected function enqueue($asset): void
	{
		if ($asset->type === self::STYLE) {
			wp_enqueue_style($asset->id, $asset->url, $asset->depends, $asset->version);
		} else {
			wp_enqueue_script($asset->id, $asset->url, $asset->depends, $asset->version, $asset->footer);
		}
	}

	/**
	 * @param int  $target
	 * @param null $hook
	 *
	 * @return array
	 */
	protected function filter(int $target, $hook = null): array
	{
		return array_filter($this->assets, static function ($asset) use ($target, $hook) {
			if (!($asset->target & $target)) {
				return false;
			}

			if (($target === self::BACK) && !empty($hook) && !empty($asset->hooks) && is_admin() && !in_array($hook, $asset->hooks, false)) {
				return false;
			}

			if (is_callable($asset->on)) {
				return call_user_func($asset->on, $hook);
			}

			return true;
		});
	}

	/**
	 * @param string $url
	 *
	 * @return string
	 */
	protected function getId(string $url): string
	{
		return str_replace([
			'.',
			'_',
		], '-', basename(parse_url($url, PHP_URL_PATH)));
	}

	/**
	 * @param string $file
	 * @param string $type
	 * @param string $path
	 *
	 * @return string
	 */
	protected function getUrl(string $file, string $type, string $path = ''): string
	{
		return plugins_url($this->paths[$type] . $file, $path);
	}

	/**
	 * @param string $source
	 * @param bool   $url
	 *
	 * @return string
	 */
	protected function getType(string $source, bool $url): string
	{
		if ($url) {
			$source = parse_url($source, PHP_URL_PATH);
		}

		return pathinfo($source, PATHINFO_EXTENSION);
	}

}