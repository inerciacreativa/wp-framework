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
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 * Adds a new field.
	 *
	 * @param string          $id
	 * @param string|callable $callback
	 * @param string          $label
	 * @param array           $attributes
	 * @param mixed           $value
	 * @param string|array    $selected
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addField(string $id, $callback, string $label, array $attributes = [], $value = null, array $selected = []): self
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
	 *
	 * @throws \InvalidArgumentException
	 */
	public function text(string $id, string $label, array $attributes = [], $value = null): self
	{
		return $this->addField($id, 'text', $label, $attributes, $value);
	}

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 * @param string $value
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function textarea(string $id, string $label, array $attributes = [], $value = null): self
	{
		return $this->addField($id, 'textarea', $label, $attributes, $value);
	}

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 * @param string $value
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function number(string $id, string $label, array $attributes = [], $value = null): self
	{
		return $this->addField($id, 'number', $label, $attributes, $value);
	}

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 * @param string $value
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function email(string $id, string $label, array $attributes = [], $value = null): self
	{
		return $this->addField($id, 'email', $label, $attributes, $value);
	}

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 * @param string $value
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function url(string $id, string $label, array $attributes = [], $value = null): self
	{
		return $this->addField($id, 'url', $label, $attributes, $value);
	}

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 * @param string $value
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function password(string $id, string $label, array $attributes = [], $value = null): self
	{
		return $this->addField($id, 'password', $label, $attributes, $value);
	}

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 * @param string $value
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function file(string $id, string $label, array $attributes = [], $value = null): self
	{
		return $this->addField($id, 'file', $label, $attributes, $value);
	}

	/**
	 * @param string $id
	 * @param array  $attributes
	 * @param string $value
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function hidden(string $id, array $attributes = [], $value = null): self
	{
		return $this->addField($id, 'hidden', '', $attributes, $value);
	}

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 * @param string $value
	 * @param array  $selected
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function checkbox(string $id, string $label, array $attributes = [], $value = null, array $selected = []): self
	{
		return $this->addField($id, 'checkbox', $label, $attributes, $value, $selected);
	}

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 * @param string $value
	 * @param array  $selected
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function radio(string $id, string $label, array $attributes = [], $value = null, array $selected = []): self
	{
		return $this->addField($id, 'radio', $label, $attributes, $value, $selected);
	}

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 * @param string $value
	 * @param array  $selected
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function select(string $id, string $label, array $attributes = [], $value = null, array $selected = []): self
	{
		return $this->addField($id, 'select', $label, $attributes, $value, $selected);
	}

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 * @param string $value
	 * @param array  $selected
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function choices(string $id, string $label, array $attributes = [], $value = null, array $selected = []): self
	{
		return $this->addField($id, 'choices', $label, $attributes, $value, $selected);
	}

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 * @param array  $selected
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function post_types(string $id, string $label, array $attributes = [], array $selected = []): self
	{
		return $this->addField($id, 'post_types', $label, $attributes, null, $selected);
	}

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 * @param array  $selected
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function taxonomies(string $id, string $label, array $attributes = [], array $selected = []): self
	{
		return $this->addField($id, 'taxonomies', $label, $attributes, null, $selected);
	}

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 * @param array  $selected
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function terms(string $id, string $label, array $attributes = [], array $selected = []): self
	{
		return $this->addField($id, 'terms', $label, $attributes, null, $selected);
	}

	/**
	 * @param string $id
	 * @param string $label
	 * @param array  $attributes
	 * @param array  $selected
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function image_sizes(string $id, string $label, array $attributes = [], array $selected = []): self
	{
		return $this->addField($id, 'image_sizes', $label, $attributes, null, $selected);
	}

}
