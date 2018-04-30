<?php

namespace ic\Framework\Settings\Form;

use ic\Framework\Settings\SettingsForm;
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
			'fields'     => [
				'text',
				'password',
				'hidden',
				'email',
				'url',
				'file',
				'number',
				'textarea',
			],
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
			'fields'     => [
				'post_types',
				'taxonomies',
				'terms',
				'image_sizes',
			],
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
	 * @param array|bool      $selected
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct(Section $section, string $id, $callback, string $label, array $attributes = [], $value = null, $selected = null)
	{
		$this->section = $section;
		$this->label   = $label;
		$this->id      = $id;
		$this->type    = \is_string($callback) ? $callback : 'custom';

		if (\in_array($this->type, ['select', 'choices'])) {
			$value = (array) $value;
		}

		if (\in_array($this->type, ['checkbox', 'radio'])) {
			$selected = (bool) $selected;
		}

		[
			$this->callback,
			$this->parameters,
		] = $this->getCallback($callback, compact('id', 'value', 'selected', 'attributes'));
	}

	/**
	 * Return the ID.
	 *
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * Return the type.
	 *
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Register the Field.
	 */
	public function register(): void
	{
		$arguments = [];

		if ($id = $this->getLabel()) {
			$arguments['label_for'] = $id;
		}

		add_settings_field($this->id, $this->label, $this, $this->getPageId(), $this->getSectionId(), $arguments);
	}

	/**
	 * Render the field.
	 */
	public function __invoke(): void
	{
		echo \call_user_func_array($this->callback, $this->parameters);
	}

	/**
	 * @return string
	 */
	protected function getSectionId(): string
	{
		return $this->section->getId();
	}

	/**
	 * @return string
	 */
	protected function getPageId(): string
	{
		return $this->section->getPage()->id();
	}

	/**
	 * @return SettingsForm
	 */
	protected function getForm(): SettingsForm
	{
		return $this->section->getPage()->getForm();
	}

	/**
	 * Return the field ID for the label element.
	 *
	 * @return string|null
	 */
	protected function getLabel(): ?string
	{
		if ($this->getAttribute('label') || $this->getAttribute('expanded')) {
			return null;
		}

		return $this->getAttribute('id', str_replace('.', '-', $this->id));
	}

	/**
	 * Set the callback to render the Field.
	 *
	 * @param string|callable $callback
	 * @param array           $parameters
	 *
	 * @return array
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function getCallback($callback, array $parameters): array
	{
		$form = $this->getForm();

		if (\is_string($callback) && method_exists($form, $callback)) {
			return [
				[$form, $callback],
				$this->getParameters($callback, $parameters),
			];
		}

		if (\is_callable($callback)) {
			return [$callback, $parameters];
		}

		throw new \InvalidArgumentException(sprintf('Not a valid callback (ID %s).', $this->id));
	}

	/**
	 * Get an attribute from the $parameters array.
	 *
	 * @param string      $name
	 * @param string|null $default
	 *
	 * @return string|null
	 */
	protected function getAttribute(string $name, $default = null): ?string
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
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function getParameters(string $field, array $parameters): array
	{
		foreach (static::$fieldParameters as $group) {
			if (\in_array($field, $group['fields'], false)) {
				return Arr::only($parameters, $group['parameters']);
			}
		}

		throw new \InvalidArgumentException(sprintf('Unknown field "%s" (ID %s).', $field, $this->id));
	}

}