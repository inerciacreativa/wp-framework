<?php

namespace ic\Framework\Settings\Form;

use ic\Framework\Support\Arr;
use InvalidArgumentException;

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
			'fields'     => ['checkbox', 'radio'],
			'parameters' => ['id', 'value', 'checked', 'attributes'],
		],
        [
            'fields'     => ['select', 'choices'],
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
	 * @throws InvalidArgumentException
	 */
	public function __construct(Section $section, string $id, $callback, string $label, array $attributes = [], $value = null, $selected = null)
	{
		$this->section = $section;
		$this->label   = $label;
		$this->id      = $id;
		$this->type    = is_string($callback) ? $callback : 'custom';

		if (in_array($this->type, ['select', 'choices'])) {
			$value = (array) $value;
		}

		if (in_array($this->type, ['checkbox', 'radio'])) {
			$checked = (bool) $selected;
		} else{
            $checked = null;
        }

		$form = $this->section->page()->form();

		[
			$this->callback,
			$this->parameters,
		] = self::callback($form, $callback, compact('id', 'value', 'checked', 'selected', 'attributes'));
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
	 * Return the type.
	 *
	 * @return string
	 */
	public function type(): string
	{
		return $this->type;
	}

	/**
	 * Register the Field.
	 */
	public function register(): void
	{
		$page      = $this->section->page()->id();
		$section   = $this->section->id();
		$arguments = [];

		if ($id = self::label($this->parameters['attributes'], $this->id)) {
			$arguments['label_for'] = $id;
		}

		add_settings_field($this->id, $this->label, $this, $page, $section, $arguments);
	}

	/**
	 * Render the field.
	 */
	public function __invoke(): void
	{
		echo call_user_func_array($this->callback, $this->parameters);
	}

	/**
	 * Set the callback to render the field.
	 *
	 * @param Form            $form
	 * @param string|callable $callback
	 * @param array           $parameters
	 *
	 * @return array
	 *
	 * @throws InvalidArgumentException
	 */
	protected static function callback(Form $form, $callback, array $parameters): array
	{
		if (is_string($callback) && method_exists($form, $callback)) {
			return [
				[$form, $callback],
				self::parameters($callback, $parameters),
			];
		}

		if (is_callable($callback)) {
			return [$callback, $parameters];
		}

		throw new InvalidArgumentException(sprintf('Not a valid callback (ID %s).', $parameters['id']));
	}

	/**
	 * Return the allowed parameters for the field type.
	 *
	 * @param string $field
	 * @param array  $parameters
	 *
	 * @return array
	 *
	 * @throws InvalidArgumentException
	 */
	protected static function parameters(string $field, array $parameters): array
	{
		foreach (self::$fieldParameters as $group) {
			if (in_array($field, $group['fields'], false)) {
				return Arr::only($parameters, $group['parameters']);
			}
		}

		throw new InvalidArgumentException(sprintf('Unknown field "%s" (ID %s).', $field, $parameters['id']));
	}

	/**
	 * Return the field ID for the label element.
	 *
	 * @param array  $attributes
	 * @param string $id
	 *
	 * @return string|null
	 */
	protected static function label(array $attributes, string $id): ?string
	{
		if (Arr::has($attributes, 'label') || Arr::has($attributes, 'expanded')) {
			return null;
		}

		return Arr::get($attributes, 'id', str_replace('.', '-', $id));
	}

}
