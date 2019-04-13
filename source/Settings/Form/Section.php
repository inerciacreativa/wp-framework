<?php

namespace ic\Framework\Settings\Form;

use ic\Framework\Settings\Page\Page;
use ic\Framework\Settings\Settings;
use InvalidArgumentException;

/**
 * Class Section
 *
 * @package ic\Framework\Settings\Form
 */
class Section
{

	/**
	 * @var Page
	 */
	protected $page;

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var Field[]
	 */
	protected $fields = [];

	/**
	 * @var callable
	 */
	protected $content;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string|callable
	 */
	protected $description;

	/**
	 * Section constructor.
	 *
	 * @param Page     $page
	 * @param string   $id
	 * @param callable $content
	 */
	public function __construct(Page $page, string $id, callable $content)
	{
		$this->page    = $page;
		$this->id      = $id;
		$this->content = $content;
	}

	/**
	 * Register the section.
	 *
	 * @return $this
	 */
	public function register(): self
	{
		call_user_func($this->content, $this);

		// Register section only if custom or an option section that doesn't exists
		if (!Settings::isDefaultSection($this->page->id(), $this->id)) {
			add_settings_section($this->id, $this->title, $this, $this->page->id());
		}

		foreach ($this->fields as $field) {
			$field->register();
		}

		return $this;
	}

	/**
	 * Return the ID.
	 *
	 * @return string
	 */
	public function id(): string
	{
		return $this->id;
	}

	/**
	 * Return the page.
	 *
	 * @return Page
	 */
	public function page(): Page
	{
		return $this->page;
	}

	/**
	 * @param string $title
	 *
	 * @return $this
	 */
	public function title(string $title): self
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * @param string|callable $description
	 *
	 * @return $this
	 */
	public function description($description): self
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * Return all fields.
	 *
	 * @return Field[]
	 */
	public function fields(): array
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function field(string $id, $callback, string $label, array $attributes = [], $value = null, array $selected = []): self
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
	 * @throws InvalidArgumentException
	 */
	public function text(string $id, string $label, array $attributes = [], $value = null): self
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function textarea(string $id, string $label, array $attributes = [], $value = null): self
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function number(string $id, string $label, array $attributes = [], $value = null): self
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function email(string $id, string $label, array $attributes = [], $value = null): self
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function url(string $id, string $label, array $attributes = [], $value = null): self
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function password(string $id, string $label, array $attributes = [], $value = null): self
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function file(string $id, string $label, array $attributes = [], $value = null): self
	{
		return $this->field($id, 'file', $label, $attributes, $value);
	}

	/**
	 * @param string $id
	 * @param array  $attributes
	 * @param string $value
	 *
	 * @return $this
	 *
	 * @throws InvalidArgumentException
	 */
	public function hidden(string $id, array $attributes = [], $value = null): self
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function checkbox(string $id, string $label, array $attributes = [], $value = null, array $selected = []): self
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function radio(string $id, string $label, array $attributes = [], $value = null, array $selected = []): self
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function select(string $id, string $label, array $attributes = [], $value = null, array $selected = []): self
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function choices(string $id, string $label, array $attributes = [], $value = null, array $selected = []): self
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function post_types(string $id, string $label, array $attributes = [], array $selected = []): self
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function taxonomies(string $id, string $label, array $attributes = [], array $selected = []): self
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function terms(string $id, string $label, array $attributes = [], array $selected = []): self
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
	 *
	 * @throws InvalidArgumentException
	 */
	public function image_sizes(string $id, string $label, array $attributes = [], array $selected = []): self
	{
		return $this->field($id, 'image_sizes', $label, $attributes, null, $selected);
	}

	/**
	 * Render the description for the section.
	 */
	public function __invoke()
	{
		if (empty($this->description)) {
			return;
		}

		if (is_callable($this->description)) {
			echo call_user_func($this->description);
		}

		if (is_string($this->description)) {
			if (strpos($this->description, '<p>') === false) {
				$this->description = "<p>$this->description</p>";
			}

			echo $this->description;
		}
	}

}