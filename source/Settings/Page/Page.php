<?php

namespace ic\Framework\Settings\Page;

use ic\Framework\Hook\HookDecorator;
use ic\Framework\Html\Tag;
use ic\Framework\Settings\Form\Form;
use ic\Framework\Support\Options;

/**
 * Class Page
 *
 * @package ic\Framework\Settings
 */
abstract class Page
{

	use HookDecorator;

	/**
	 * @var string
	 */
	protected $parent;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var Options;
	 */
	protected $options;

	/**
	 * @var Form
	 */
	protected $form;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * Page constructor.
	 *
	 * @param string  $parent
	 * @param Options $options
	 * @param string  $title
	 * @param array   $config
	 */
	public function __construct(string $parent, Options $options, string $title, array $config = [])
	{
		$this->parent  = $parent;
		$this->options = $options;
		$this->title   = $title;
		$this->form    = new Form($options);
		$this->config  = array_merge([
			'menu'       => null,
			'capability' => 'manage_options',
		], $config);

		$this->hook()->on('admin_init', 'register');
		if ($menu = $this->config('menu')) {
			$this->hook()->before($menu, 'menu');
		}
	}

	/**
	 * @return string
	 */
	public function id(): string
	{
		return $this->options->id();
	}

	/**
	 * @return string
	 */
	public function parent(): string
	{
		return $this->parent;
	}

	/**
	 * @return Options
	 */
	public function options(): Options
	{
		return $this->options;
	}

	/**
	 * @return Form
	 */
	public function form(): Form
	{
		return $this->form;
	}

	/**
	 *
	 */
	abstract protected function register(): void;

	/**
	 * @return string
	 */
	abstract protected function navigation(): string;

	/**
	 * @param string      $property
	 * @param string|null $default
	 *
	 * @return string|null
	 */
	protected function config(string $property, string $default = null): ?string
	{
		return $this->config[$property] ?? $default;
	}

	/**
	 * Add the submenu page.
	 */
	protected function menu(): void
	{
		add_submenu_page($this->parent, $this->title, $this->title, $this->config('capability', 'manage_options'), $this->id(), function () {
			$this->render();
		});
	}

	/**
	 *
	 */
	protected function render(): void
	{
		echo Tag::div(['class' => 'wrap'], [
			Tag::h1($this->title),
			$this->capture(static function (string $id) {
				settings_errors($id);
			}),
			$this->navigation(),
			$this->form->open(['action' => $this->parent]),
			$this->capture(static function (string $id) {
				do_settings_sections($id);
				submit_button();
			}),
			$this->form->close(),
		]);
	}

	/**
	 * @param callable $callback
	 *
	 * @return string
	 */
	protected function capture(callable $callback): string
	{
		ob_start();

		$callback($this->id());

		return ob_get_clean();
	}

}