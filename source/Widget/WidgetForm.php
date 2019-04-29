<?php

namespace ic\Framework\Widget;

use ic\Framework\Form\AdvancedForm;
use ic\Framework\Http\Input;

/**
 * Class WidgetForm
 *
 * @package ic\Framework\Widget
 */
class WidgetForm extends AdvancedForm
{

	/**
	 * @var Widget
	 */
	private $widget;

	/**
	 * WidgetForm constructor.
	 *
	 * @param Widget       $widget
	 * @param array|object $model
	 */
	public function __construct(Widget $widget, $model)
	{
		$this->widget = $widget;

		parent::__construct($widget->id(), $model);
	}

	/**
	 * @inheritdoc
	 */
	protected function getIdAttribute(string $id, array $attributes = []): string
	{
		return $this->widget->getFieldId($id);
	}

	/**
	 * @inheritdoc
	 */
	protected function getNameAttribute(string $id, string $tag, array $attributes = []): string
	{
		return $this->widget->getFieldName($id);
	}

	/**
	 * @inheritdoc
	 */
	protected function getSessionValue($id)
	{
		if ($this->isSessionEmpty() || !Input::getInstance()->has([
				'id_base',
				'widget_number',
			])) {
			return null;
		}

		$input     = Input::getInstance();
		$sessionId = implode('.', [
			'widget-' . $input->get('id_base'),
			$input->get('widget_number'),
			$id,
		]);

		return $input->get($sessionId, false);
	}

}