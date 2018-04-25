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
     * @var string
     */
    private $class;

    /**
     * @var WidgetProxy
     */
    private $proxy;

    /**
     * @return static
     */
    public static function create()
    {
        $argument = \func_num_args() === 1 ? func_get_arg(0) : null;

        return new static($argument);
    }

    /**
     * Widget constructor.
     */
    public function __construct()
    {
        $this->class = static::class;
        $this->proxy = new WidgetProxy($this);

        $this->register();
    }

    /**
     * @return $this
     */
    protected function register()
    {
        $this->setHook()->on('widgets_init', function () {
            global $wp_widget_factory;

            $wp_widget_factory->widgets[$this->class] = $this->proxy;
        });

        return $this;
    }

    /**
     * @return string
     */
    abstract public function id();

    /**
     * @return string
     */
    abstract public function name();

    /**
     * @return string
     */
    public function description()
    {
        return '';
    }

    /**
     * @param Tag   $widget
     * @param Tag   $title
     * @param array $instance
     *
     * @return string
     */
    abstract protected function frontend(Tag $widget, Tag $title, array $instance);

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
    protected function sanitize(array $instance)
    {
        return $instance;
    }

    /**
     * @param array $instance
     * @param array $values
     *
     * @return array
     */
    public function update(array $instance, array $values)
    {
        foreach ($instance as $key => $value) {
            if (!isset($values[$key])) {
                $void = null;
                $type = gettype($value);
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
    public function display(array $instance, array $arguments)
    {
        $widget          = Tag::create($this->getTag($arguments['after_widget'], 'div'));
        $widget['class'] = $this->getClasses($arguments['before_widget'], 'widget', $instance['classes']);

        $title          = Tag::create($this->getTag($arguments['after_title'], 'h2'), [], $instance['title']);
        $title['class'] = $this->getClasses($arguments['before_title'], 'widget-title');

        echo $this->frontend($widget, $title, $instance);
    }

    /**
     * @param array $instance
     */
    public function configure(array $instance)
    {
        $form = new WidgetForm($this, $instance);

        echo Tag::div(['class' => 'ic-widget'], [
            Tag::p($form->text('title', '', ['class' => 'widefat', 'label' => __('Widget title:', 'ic-framework')])),
            Tag::p($form->text('classes', '', ['class' => 'widefat', 'label' => __('Class names:', 'ic-framework')])),
            $this->backend($instance, $form),
        ]);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getFieldId($name)
    {
        return $this->proxy->get_field_id($name);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getFieldName($name)
    {
        return $this->proxy->get_field_name($name);
    }

    /**
     * @param string $string
     * @param string $default
     *
     * @return string
     */
    protected function getTag($string, $default = '')
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
    protected function getClasses($string, $default, $extra = '')
    {
        $classes = $default;

        if (preg_match('/ class="(.*)"/', $string, $matches)) {
            $classes = $matches[1];
        }

        if (!empty($extra)) {
            $classes .= ' '. trim($extra);
        }

        return $classes;
    }

}