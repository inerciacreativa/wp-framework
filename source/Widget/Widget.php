<?php

namespace ic\Framework\Widget;

use ic\Framework\Hook\HookDecorator;
use ic\Framework\Html\Tag;

/**
 * Class Widget
 *
 * @package ic\Framework\Widget
 */
abstract class Widget
{

	use HookDecorator;

	/**
	 * @var WidgetProxy
	 */
	private $proxy;

	/**
	 * Widget constructor.
	 */
	public function __construct()
	{
		$this->proxy = new WidgetProxy($this);

		$this->register();
	}

	/**
	 * @return $this
	 */
	protected function register(): self
	{
		$this->hook()->on('widgets_init', function () {
			register_widget($this->proxy);
		});

		return $this;
	}

	/**
	 * @return string
	 */
	abstract public function id(): string;

	/**
	 * @return string
	 */
	abstract public function name(): string;

	/**
	 * @return string
	 */
	public function description(): string
	{
		return '';
	}

	/**
	 * @param array $instance
	 * @param Tag   $widget
	 * @param Tag   $title
	 */
	abstract protected function frontend(array $instance, Tag $widget, Tag $title): void;

	/**
	 * @param array      $instance
	 * @param WidgetForm $form
	 *
	 * @return string|array
	 */
	abstract protected function backend(array $instance, WidgetForm $form);

	/**
	 * @param array $instance
	 *
	 * @return array
	 */
	protected function sanitize(array $instance): array
	{
		return $instance;
	}

	/**
	 * @param array $instance
	 * @param array $values
	 *
	 * @return array
	 */
	public function update(array $instance, array $values): array
	{
		foreach ($instance as $key => $value) {
			if (!isset($values[$key])) {
				$void = null;
				$type = \gettype($value);
				settype($void, $type);

				$values[$key] = $void;
			}
		}

		return $this->sanitize(array_merge($instance, $values));
	}

	/**
	 * @param array $instance
	 * @param array $arguments
	 */
	public function display(array $instance, array $arguments): void
	{
		$widget          = Tag::make($this->getTag($arguments['after_widget'], 'div'));
		$widget['class'] = $this->getClasses($arguments['before_widget'], 'widget', $instance['classes']);

		$title          = Tag::make($this->getTag($arguments['after_title'], 'h2'), [], $instance['title']);
		$title['class'] = $this->getClasses($arguments['before_title'], 'widget-title');

		$this->frontend($instance, $widget, $title);
	}

	/**
	 * @param array $instance
	 */
	public function configure(array $instance): void
	{
		$form = new WidgetForm($this, $instance);

		echo Tag::div(['class' => 'ic-widget'], [
			Tag::p($form->text('title', '', [
				'class' => 'widefat',
				'label' => __('Widget title:', 'ic-framework'),
			])),
			Tag::p($form->text('classes', '', [
				'class' => 'widefat',
				'label' => __('Class names:', 'ic-framework'),
			])),
			$this->backend($instance, $form),
		]);
	}

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	public function getFieldId(string $name): string
	{
		return $this->proxy->get_field_id($name);
	}

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	public function getFieldName(string $name): string
	{
		return $this->proxy->get_field_name($name);
	}

	/**
	 * @param string $string
	 * @param string $default
	 *
	 * @return string
	 */
	protected function getTag(string $string, string $default = ''): string
	{
		$tag = preg_replace('/[^[:alnum:]]/', '', $string);

		return empty($tag) ? $default : $tag;
	}

	/**
	 * @param string $string
	 * @param string $default
	 * @param string $extra
	 *
	 * @return string
	 */
	protected function getClasses(string $string, string $default, string $extra = ''): string
	{
		$classes = $default;

		if (preg_match('/ class="(.*)"/', $string, $matches)) {
			$classes = $matches[1];
		}

		if (!empty($extra)) {
			$classes .= ' ' . trim($extra);
		}

		return $classes;
	}

}