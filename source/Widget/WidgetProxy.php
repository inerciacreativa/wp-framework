<?php

namespace ic\Framework\Widget;

/**
 * Class WidgetProxy
 *
 * @package ic\Framework\Widget
 */
class WidgetProxy extends \WP_Widget
{

    /**
     * @var Widget
     */
    private $widget;

    /**
     * WidgetShim constructor.
     *
     * @param Widget $widget
     */
    public function __construct(Widget $widget)
    {
        $this->widget = $widget;

        $options = [
            'classname'   => 'widget-' . $widget->id(),
            'description' => $widget->description(),
        ];

        parent::__construct($widget->id(), $widget->name(), $options);
    }

    /**
     * @inheritdoc
     */
    public function widget($arguments, $instance)
    {
        $this->widget->display($instance, $arguments);
    }

    /**
     * @inheritdoc
     */
    public function update($new_instance, $old_instance)
    {
        return $this->widget->update($old_instance, $new_instance);
    }

    /**
     * @inheritdoc
     */
    public function form($instance)
    {
        $this->widget->configure($instance);
    }

}