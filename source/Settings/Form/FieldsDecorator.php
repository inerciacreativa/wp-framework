<?php

namespace ic\Framework\Settings\Form;

/**
 * Class FieldsDecorator
 */
trait FieldsDecorator
{

    /**
     * @var Field[]
     */
    protected $fields = [];

    /**
     * Return all fields.
     *
     * @return Field[]
     */
    public function fields()
    {
        return $this->fields;
    }

    /**
     * Add a new field.
     *
     * @param string          $id
     * @param string|callable $callback
     * @param string          $label
     * @param array           $attributes
     * @param mixed           $value
     * @param string|array    $selected
     *
     * @return $this
     */
    public function field($id, $callback, $label, array $attributes = [], $value = null, $selected = null)
    {
        $this->fields[$id] = new Field($this, $id, $callback, $label, $attributes, $value, $selected);

        return $this;
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param string $value
     *
     * @return $this
     */
    public function text($id, $label, array $attributes = [], $value = null)
    {
        return $this->field($id, 'text', $label, $attributes, $value);
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param string $value
     *
     * @return $this
     */
    public function textarea($id, $label, array $attributes = [], $value = null)
    {
        return $this->field($id, 'textarea', $label, $attributes, $value);
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param string $value
     *
     * @return $this
     */
    public function number($id, $label, array $attributes = [], $value = null)
    {
        return $this->field($id, 'number', $label, $attributes, $value);
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param string $value
     *
     * @return $this
     */
    public function email($id, $label, array $attributes = [], $value = null)
    {
        return $this->field($id, 'email', $label, $attributes, $value);
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param string $value
     *
     * @return $this
     */
    public function url($id, $label, array $attributes = [], $value = null)
    {
        return $this->field($id, 'url', $label, $attributes, $value);
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param string $value
     *
     * @return $this
     */
    public function password($id, $label, array $attributes = [], $value = null)
    {
        return $this->field($id, 'password', $label, $attributes, $value);
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param string $value
     *
     * @return $this
     */
    public function file($id, $label, array $attributes = [], $value = null)
    {
        return $this->field($id, 'file', $label, $attributes, $value);
    }

    /**
     * @param string $id
     * @param array  $attributes
     * @param string $value
     *
     * @return $this
     */
    public function hidden($id, array $attributes = [], $value = null)
    {
        return $this->field($id, 'hidden', '', $attributes, $value);
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param string $value
     * @param array  $selected
     *
     * @return $this
     */
    public function checkbox($id, $label, array $attributes = [], $value = null, $selected = [])
    {
        return $this->field($id, 'checkbox', $label, $attributes, $value, $selected);
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param string $value
     * @param array  $selected
     *
     * @return $this
     */
    public function radio($id, $label, array $attributes = [], $value = null, $selected = [])
    {
        return $this->field($id, 'radio', $label, $attributes, $value, $selected);
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param string $value
     * @param array  $selected
     *
     * @return $this
     */
    public function select($id, $label, array $attributes = [], $value = null, $selected = [])
    {
        return $this->field($id, 'select', $label, $attributes, $value, $selected);
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param string $value
     * @param array  $selected
     *
     * @return $this
     */
    public function choices($id, $label, array $attributes = [], $value = null, $selected = [])
    {
        return $this->field($id, 'choices', $label, $attributes, $value, $selected);
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param array  $selected
     *
     * @return $this
     */
    public function post_types($id, $label, array $attributes = [], $selected = [])
    {
        return $this->field($id, 'post_types', $label, $attributes, null, $selected);
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param array  $selected
     *
     * @return $this
     */
    public function taxonomies($id, $label, array $attributes = [], $selected = [])
    {
        return $this->field($id, 'taxonomies', $label, $attributes, null, $selected);
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param array  $selected
     *
     * @return $this
     */
    public function terms($id, $label, array $attributes = [], $selected = [])
    {
        return $this->field($id, 'terms', $label, $attributes, null, $selected);
    }

    /**
     * @param string $id
     * @param string $label
     * @param array  $attributes
     * @param array  $selected
     *
     * @return $this
     */
    public function image_sizes($id, $label, array $attributes = [], $selected = [])
    {
        return $this->field($id, 'image_sizes', $label, $attributes, null, $selected);
    }

}