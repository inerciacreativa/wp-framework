<?php

namespace ic\Framework\Settings\Form;

use ic\Framework\Debug\Debug;

use ic\Framework\Support\Arr;

/**
 * Class Field
 *
 * @package ic\Framework\Settings\Form
 */
class Field
{

    /**
     * @var Section
     */
    protected $section;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var array
     */
    protected static $fieldParameters = [
        [
            'fields'     => ['text', 'password', 'hidden', 'email', 'url', 'file', 'number', 'textarea'],
            'parameters' => ['id', 'value', 'attributes'],
        ],
        [
            'fields'     => ['checkbox', 'radio', 'select', 'choices'],
            'parameters' => ['id', 'value', 'selected', 'attributes'],
        ],
        [
            'fields'     => ['submit', 'button', 'reset', 'image'],
            'parameters' => ['value', 'attributes'],
        ],
        [
            'fields'     => ['post_types', 'taxonomies', 'terms', 'image_sizes'],
            'parameters' => ['id', 'selected', 'attributes'],
        ],
    ];

    /**
     * Field constructor.
     *
     * @param Section         $section
     * @param string          $id
     * @param string|callable $callback
     * @param string          $label
     * @param array           $attributes
     * @param mixed           $value
     * @param string|array    $selected
     */
    public function __construct(Section $section, $id, $callback, $label, array $attributes = [], $value = null, $selected = null)
    {
        $this->section = $section;
        $this->label   = $label;
        $this->id      = $id;
        $this->type    = is_string($callback) ? $callback : 'custom';

        if (is_string($callback) && in_array($callback, ['select', 'choices'], false)) {
            $value = (array)$value;
        }

        $this->callback($callback, compact('id', 'value', 'selected', 'attributes'));
    }

    /**
     * Return the ID.
     *
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Return the type.
     *
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Register the Field.
     */
    public function register()
    {
        $arguments = [];

        if ($id = $this->label()) {
            $arguments['label_for'] = $id;
        }

        add_settings_field($this->id, $this->label, $this, $this->section->page()->id(), $this->section->id(), $arguments);
    }

    /**
     * Render the field.
     */
    public function __invoke()
    {
        if (is_callable($this->callback)) {
            echo call_user_func_array($this->callback, $this->parameters);
        }
    }

    /**
     * Return the field ID for the label element.
     *
     * @return string|null
     */
    protected function label()
    {
        if ($this->attribute('label') || $this->attribute('expanded')) {
            return null;
        }

        return $this->attribute('id', str_replace('.', '-', $this->id));
    }

    /**
     * Set the callback to render the Field.
     *
     * @param string|callable $callback
     * @param array           $parameters
     */
    protected function callback($callback, $parameters)
    {
        $form = $this->section->page()->form();

        if (is_string($callback) && method_exists($form, $callback)) {
            $this->callback   = [$form, $callback];
            $this->parameters = $this->parameters($callback, $parameters);
        } elseif (is_callable($callback)) {
            $this->callback   = $callback;
            $this->parameters = $parameters;
        } else {
            Debug::error(sprintf('Not a valid callback (ID %s).', $this->id), 'Settings Field');
        }
    }

    /**
     * Get an attribute from the $parameters array.
     *
     * @param string      $name
     * @param string|null $default
     *
     * @return string
     */
    protected function attribute($name, $default = null)
    {
        return Arr::get($this->parameters, 'attributes.' . $name, $default);
    }

    /**
     * Return the allowed parameters for the Field type.
     *
     * @param string $field
     * @param array  $parameters
     *
     * @return array
     */
    protected function parameters($field, $parameters)
    {
        foreach (static::$fieldParameters as $group) {
            if (in_array($field, $group['fields'], false)) {
                return Arr::only($parameters, $group['parameters']);
            }
        }

        Debug::error(sprintf('Unknown field "%s" (ID %s).', $field, $this->id), 'Settings Field');

        return $parameters;
    }

}