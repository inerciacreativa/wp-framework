<?php

namespace ic\Framework\Widget\Widgets;

use ic\Framework\Html\Tag;
use ic\Framework\Support\Arr;
use ic\Framework\Widget\Widget;
use ic\Framework\Widget\WidgetForm;

/**
 * Class CodeWidget
 *
 * @package ic\Framework\Widget\Widgets
 */
class CodeWidget extends Widget
{

	/**
	 * @inheritdoc
	 */
	public function id(): string
	{
		return 'code';
	}

	/**
	 * @inheritdoc
	 */
	public function name(): string
	{
		return __('ic Framework / Code', 'ic-framework');
	}

	/**
	 * @inheritdoc
	 */
	public function description(): string
	{
		return __('Arbitrary text, HTML, or PHP Code', 'ic-framework');
	}

	/**
	 * @inheritdoc
	 */
	protected function frontend(array $instance, Tag $widget, Tag $title): void
	{
		ob_start();
		eval('?>' . Arr::get($instance, 'code'));
		$content = ob_get_clean();

		if (Arr::get($instance, 'format')) {
			$content = wpautop($content);
		}

		$widget->content($title);
		$widget->content($content);

		echo $widget;
	}

	/**
	 * @inheritdoc
	 */
	protected function backend(array $instance, WidgetForm $form)
	{
		return [
			Tag::p($form->textarea('code', '', ['label' => __('Code:', 'ic-framework')])),
			Tag::p($form->checkbox('format', 1, false, ['label' => __('Automatically add paragraphs', 'ic-framework')])),
		];
	}

}