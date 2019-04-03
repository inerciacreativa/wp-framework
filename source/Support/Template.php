<?php

namespace ic\Framework\Support;

use Twist\App\AppException;
use Twist\Twist;
use Twist\View\Twig\TwigView;
use RuntimeException;
use InvalidArgumentException;

/**
 * Class Template
 *
 * @package ic\Framework\Support
 */
class Template
{

	protected static $types = [];

	/**
	 * @return array
	 */
	public static function types(): array
	{
		if (empty(static::$types)) {
			static::$types['php'] = 'PHP';

			if (class_exists(Twist::class)) {
				static::$types['twig'] = 'Twig';
			}
		}

		return static::$types;
	}

	/**
	 * @param string $template The name of the file template.
	 * @param array  $data     An array of variables to be used in the template.
	 * @param string $path     The absolute path to the default file template.
	 *
	 * @return string
	 */
	public static function render(string $template, array $data = [], string $path = ''): string
	{
		$class = new static();
		$types = static::types();

		if (Str::endsWith($template, '.twig')) {
			if (!isset($types['twig'])) {
				throw new InvalidArgumentException('Passed a twig template, but there is no rendering engine available');
			}

			return $class->twig($template, $data, $path);
		}

		return $class->php($template, $data, $path);
	}

	/**
	 * Renders a Twig template through the Twist view service.
	 *
	 * @see https://github.com/inerciacreativa/twist
	 *
	 * @param string $template
	 * @param array  $data
	 * @param string $path
	 *
	 * @return string
	 */
	protected function twig(string $template, array $data, string $path): string
	{
		/** @var TwigView $view */
		$view = Twist::view();

		try {
			if (!empty($path)) {
				$view->path($path);
			}

			return $view->render($template, $data);
		} catch (AppException $exception) {
			throw new RuntimeException($exception->getMessage());
		}
	}

	/**
	 * Renders a PHP template file.
	 *
	 * @param string $template
	 * @param array  $data
	 * @param string $path
	 *
	 * @return string
	 */
	protected function php(string $template, array $data, string $path): string
	{
		if ($filename = $this->locate($template, $path)) {
			if (!empty($data)) {
				extract($data, EXTR_SKIP);
			}

			ob_start();

			/** @noinspection PhpIncludeInspection */
			include $filename;

			return ob_get_clean();
		}

		throw new RuntimeException(sprintf('Template "%s" not found', $template));
	}

	/**
	 * Locate a file template and return the path.
	 *
	 * @param string $template
	 * @param string $path
	 *
	 * @return string|null
	 */
	protected function locate(string $template, string $path): ?string
	{
		foreach ($this->locations($template, $path) as $filename) {
			if (file_exists($filename)) {
				return $filename;
			}
		}

		return null;
	}

	/**
	 * Build an array with all possible templates.
	 *
	 * @param string $template
	 * @param string $path
	 *
	 * @return array
	 */
	protected function locations(string $template, string $path): array
	{
		$template = ltrim($template, '/');
		$path     = rtrim($path, '/');

		$locations = array_unique(array_map(static function ($location) use ($template) {
			return "$location/$template";
		}, [get_stylesheet_directory(), get_template_directory()]));

		if (!empty($path)) {
			$locations[] = "$path/$template";
		}

		return $locations;
	}

}