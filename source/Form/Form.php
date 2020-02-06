<?php

namespace ic\Framework\Form;

use ic\Framework\Data\Collection;
use ic\Framework\Html\Tag;
use ic\Framework\Http\Input;
use ic\Framework\Support\Arr;
use ic\Framework\Support\Data;

/**
 * Class Form
 *
 * @package ic\Framework\Form
 */
class Form
{

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var array
	 */
	protected $model;

	/**
	 * @var string
	 */
	protected $method;

	/**
	 * @var string
	 */
	protected $action;

	/**
	 * @var bool
	 */
	protected $opened = false;

	/**
	 * @var array
	 */
	protected static $reserved = ['method', 'url', 'files', 'action'];

	/**
	 * @var array
	 */
	protected static $skipValueTypes = [
		'file',
		'password',
		'checkbox',
		'radio',
	];

	/**
	 * @var array
	 */
	protected static $spoofedMethods = ['DELETE', 'PATCH', 'PUT'];

	/**
	 * @var array
	 */
	protected static $extraAttributes = [
		'prepend',
		'append',
		'label',
		'description',
	];

	/**
	 * Form constructor.
	 *
	 * @param string       $id
	 * @param array|object $model
	 */
	public function __construct(string $id, $model)
	{
		$this->id    = $id;
		$this->model = Arr::items($model);
	}

	/**
	 * @return string
	 */
	public function id(): string
	{
		return $this->id;
	}

	/**
	 * Open up a new HTML form.
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function open(array $options = []): string
	{
		$this->opened = true;

		$method = strtoupper(Arr::get($options, 'method', 'POST'));

		$attributes = [
			'method'         => $this->getMethod($method),
			'action'         => $this->getAction($options),
			'accept-charset' => 'UTF-8',
		];

		if (!empty($options['files'])) {
			$options['enctype'] = 'multipart/form-data';
		}

		$attributes = array_merge($attributes, Arr::except($options, static::$reserved));

		return Tag::form($attributes, $this->getHiddenFields($method))->open();
	}

	/**
	 * Close the current form.
	 *
	 * @return string
	 */
	public function close(): string
	{
		$this->opened = false;
		$this->action = null;

		return Tag::form()->close();
	}

	/**
	 * Create a form label element.
	 *
	 * @param string $id
	 * @param string $value
	 * @param array  $attributes
	 *
	 * @return mixed
	 */
	public function label(string $id, string $value, array $attributes = [])
	{
		if (!isset($attributes['for'])) {
			$attributes['for'] = $this->getIdAttribute($id, $attributes);
		}

		return Tag::label($attributes, $value);
	}

	/**
	 * Create a form input field.
	 *
	 * @param string $type
	 * @param string $id
	 * @param mixed  $value
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function input(string $type, string $id, $value = null, array $attributes = []): string
	{
		if (!isset($attributes['skipValue']) && !in_array($type, static::$skipValueTypes, false)) {
			$value = $this->getValueAttribute($id, $value);
		}

		unset($attributes['skipValue']);

		$attributes = array_merge($attributes, compact('type', 'value'));

		return $this->render($id, 'input', $attributes);
	}

	/**
	 * @param string $id
	 * @param string $tag
	 * @param array  $attributes
	 * @param mixed  $content
	 *
	 * @return string
	 */
	protected function render(string $id, string $tag, array &$attributes, $content = null): string
	{
		$attributes['id']   = $this->getIdAttribute($id, $attributes);
		$attributes['name'] = $this->getNameAttribute($id, $tag, $attributes);

		if ($tag !== 'select') {
			unset($attributes['multiple']);
		}

		$options     = Arr::only($attributes, static::$extraAttributes);
		$attributes  = Arr::except($attributes, static::$extraAttributes);
		$label       = Arr::get($options, 'label');
		$description = Arr::get($options, 'description');

		if ($description) {
			$attributes['aria-describedby'] = $attributes['id'] . '-description';
		}

		$field = Tag::make($tag, $attributes, $content);

		if ($label) {
			if ($tag === 'input' && ($attributes['type'] === 'checkbox' || $attributes['type'] === 'radio')) {
				$field = $this->label($id, $field . ' ' . $label, ['for' => $attributes['id']]);
			} else {
				$field = $this->label($id, $label, ['for' => $attributes['id']]) . ' ' . $field;
			}
		}

		if ($description) {
			$field .= Tag::p([
				'class' => 'description',
				'id'    => $attributes['id'] . '-description',
			], $description);
		}

		$field = Data::value(Arr::get($options, 'prepend')) . $field . Data::value(Arr::get($options, 'append'));

		return $field;
	}

	/**
	 * Create a text input field.
	 *
	 * @param string $id
	 * @param mixed  $value
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function text(string $id, $value = null, array $attributes = []): string
	{
		return $this->input('text', $id, $value, $attributes);
	}

	/**
	 * Create a password input field.
	 *
	 * @param string $id
	 * @param mixed  $value
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function password(string $id, $value = null, array $attributes = []): string
	{
		return $this->input('text', $id, $value, $attributes);
	}

	/**
	 * Create a hidden input field.
	 *
	 * @param string $id
	 * @param mixed  $value
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function hidden(string $id, $value = null, array $attributes = []): string
	{
		return $this->input('hidden', $id, $value, $attributes);
	}

	/**
	 * Create a special hidden input field.
	 *
	 * @param string $id
	 * @param mixed  $value
	 *
	 * @return string
	 */
	public function special(string $id, $value): string
	{
		return $this->hidden($id, $value, ['name' => $id, 'skipValue' => true]);
	}

	/**
	 * Create an e-mail input field.
	 *
	 * @param string $id
	 * @param mixed  $value
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function email(string $id, $value = null, array $attributes = []): string
	{
		return $this->input('email', $id, $value, $attributes);
	}

	/**
	 * Create a url input field.
	 *
	 * @param string $id
	 * @param mixed  $value
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function url(string $id, $value = null, array $attributes = []): string
	{
		return $this->input('url', $id, $value, $attributes);
	}

	/**
	 * Create a file input field.
	 *
	 * @param string $id
	 * @param mixed  $value
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function file(string $id, $value = null, array $attributes = []): string
	{
		return $this->input('file', $id, $value, $attributes);
	}

	/**
	 * Create a number input field.
	 *
	 * @param string $id
	 * @param mixed  $value
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function number(string $id, $value = null, array $attributes = []): string
	{
		return $this->input('number', $id, (int) $value, $attributes);
	}

	/**
	 * Create a textarea input field.
	 *
	 * @param string $id
	 * @param string $value
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function textarea(string $id, $value = null, array $attributes = []): string
	{
		if (isset($attributes['size'])) {
			$size       = explode('x', $attributes['size']);
			$attributes = array_merge($attributes, [
				'cols' => (int) $size[0],
				'rows' => (int) $size[1],
			]);

			unset($attributes['size']);
		}

		$attributes['cols'] = Arr::get($attributes, 'cols', 50);
		$attributes['rows'] = Arr::get($attributes, 'rows', 10);

		$value = (string) $this->getValueAttribute($id, $value);

		return $this->render($id, 'textarea', $attributes, $value);
	}

	/**
	 * Create a checkbox input field.
	 *
	 * @param string     $id
	 * @param string|int $value
	 * @param bool       $checked
	 * @param array      $attributes
	 *
	 * @return string
	 */
	public function checkbox(string $id, $value = 1, bool $checked = false, array $attributes = []): string
	{
		if ($value === null) {
			$value = 1;
		}

		return $this->checkable('checkbox', $id, $value, $checked, $attributes);
	}

	/**
	 * Create a radio button input field.
	 *
	 * @param string     $id
	 * @param string|int $value
	 * @param bool       $checked
	 * @param array      $attributes
	 *
	 * @return string
	 */
	public function radio(string $id, $value = null, bool $checked = false, array $attributes = []): string
	{
		if ($value === null) {
			$value = $id;
		}

		return $this->checkable('radio', $id, $value, $checked, $attributes);
	}

	/**
	 * Create a checkable input field.
	 *
	 * @param string     $type
	 * @param string     $id
	 * @param string|int $value
	 * @param bool       $checked
	 * @param array      $attributes
	 *
	 * @return string
	 */
	protected function checkable(string $type, string $id, $value, bool $checked, array $attributes): string
	{
		$checked = $this->isChecked($type, $id, $value, $checked);

		if ($checked) {
			$attributes['checked'] = 'checked';
		}

		return $this->input($type, $id, $value, $attributes);
	}

	/**
	 * Get the check state for a checkable input.
	 *
	 * @param string $type
	 * @param string $id
	 * @param mixed  $value
	 * @param bool   $checked
	 *
	 * @return bool
	 */
	protected function isChecked(string $type, string $id, $value, bool $checked): bool
	{
		switch ($type) {
			case 'checkbox':
				return $this->isCheckboxChecked($id, $value, $checked);

			case 'radio':
				return $this->isRadioChecked($id, $value, $checked);
		}

		/** @noinspection TypeUnsafeComparisonInspection */
		return $this->getValueAttribute($id) == $value;
	}

	/**
	 * Get the check state for a checkbox input.
	 *
	 * @param string $id
	 * @param mixed  $value
	 * @param bool   $checked
	 *
	 * @return bool
	 */
	protected function isCheckboxChecked(string $id, $value, bool $checked): bool
	{
		if (!$this->isSessionEmpty() && ($this->getSessionValue($id) === null)) {
			return false;
		}

		if (($this->getSessionValue($id) === null) && ($this->getModelValue($id) === null)) {
			return $checked;
		}

		$posted = $this->getValueAttribute($id);

		return is_array($posted) ? in_array($value, $posted, false) : (bool) $posted;
	}

	/**
	 * Get the check state for a radio input.
	 *
	 * @param string $id
	 * @param mixed  $value
	 * @param bool   $checked
	 *
	 * @return bool
	 */
	protected function isRadioChecked(string $id, $value, bool $checked): bool
	{
		if (($this->getSessionValue($id) === null) && ($this->getModelValue($id) === null)) {
			return $checked;
		}

		/** @noinspection TypeUnsafeComparisonInspection */
		return $this->getValueAttribute($id) == $value;
	}

	/**
	 * Create a select box field.
	 *
	 * @param string $id
	 * @param array  $values
	 * @param string $selected
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function select(string $id, array $values = [], $selected = null, array $attributes = []): string
	{
		if (empty($values)) {
			$values = $this->getValueAttribute($id, $values);
		} else if (is_callable($values)) {
			$values = $values();
		}

		$selected = $this->getValueAttribute($id, $selected);
		$options  = [];

		foreach ((array) $values as $value => $display) {
			$options[] = $this->getSelectOption($display, $value, $selected);
		}

		return $this->render($id, 'select', $attributes, $options);
	}

	/**
	 * Get the select option for the given value.
	 *
	 * @param string|array $display
	 * @param string       $value
	 * @param string       $selected
	 *
	 * @return string
	 */
	protected function getSelectOption($display, $value, $selected): string
	{
		if (is_array($display)) {
			return $this->optionGroup($display, $value, $selected);
		}

		return $this->option($display, $value, $selected);
	}

	/**
	 * Create an option group form element.
	 *
	 * @param array        $options
	 * @param string       $label
	 * @param string|array $selected
	 *
	 * @return string
	 */
	protected function optionGroup(array $options, string $label, $selected): string
	{
		$group = [];

		foreach ($options as $value => $display) {
			$group[] = $this->option($display, $value, $selected);
		}

		return Tag::optgroup(['label' => $label], $group);
	}

	/**
	 * Create a select element option.
	 *
	 * @param string       $display
	 * @param string|int   $value
	 * @param string|array $selected
	 *
	 * @return string
	 */
	protected function option(string $display, $value, $selected): string
	{
		$attributes = ['value' => $value];

		if ($this->isSelectedValue($value, $selected)) {
			$attributes['selected'] = 'selected';
		}

		return Tag::option($attributes, $display);
	}

	/**
	 * Determine if the value is selected.
	 *
	 * @param string|int   $value
	 * @param string|array $selected
	 *
	 * @return bool
	 */
	protected function isSelectedValue($value, $selected): bool
	{
		if (is_array($selected)) {
			return in_array($value, $selected, false);
		}

		return ((string) $value === (string) $selected);
	}

	/**
	 * Create a choices field. It can be a select, a multiple select, a list of
	 * radio buttons or a list of checkboxes.
	 *
	 * @param string         $id
	 * @param array|callable $values
	 * @param array          $selected
	 * @param array          $attributes
	 *
	 * @return string
	 */
	public function choices(string $id, $values = [], array $selected = [], array $attributes = []): string
	{
		if (empty($values)) {
			$values = $this->getValueAttribute($id, $values);
		} else if (is_callable($values)) {
			$values = $values();
		} else if ($values instanceof Collection) {
			$values = $values->all();
		}

		$expanded = Arr::get($attributes, 'expanded', false);
		$multiple = Arr::get($attributes, 'multiple', false);

		unset($attributes['expanded']);

		if ($expanded) {
			$type        = $multiple ? 'checkbox' : 'radio';
			$legend      = Arr::pull($attributes, 'legend');
			$description = Arr::pull($attributes, 'description');
			$choices     = $this->getChoices($type, $id, $values, $selected, $attributes);
			$field       = Tag::fieldset();

			if ($legend) {
				$field->content(Tag::legend(['class' => 'screen-reader-text'], $legend));
			}

			$field->content(Tag::div(['class' => 'wp-tab-panel inside'], Tag::ul(['class' => 'categorychecklist'], $choices)));

			if ($description) {
				$id    = $this->getIdAttribute($id, $attributes);
				$field .= Tag::p([
					'class' => 'description',
					'id'    => $id . '-description',
				], $description);
			}

			return $field;
		}

		if ($multiple && !isset($attributes['size'])) {
			$attributes['size'] = count($values);
		}

		return $this->select($id, $values, $selected, $attributes);
	}

	/**
	 * @param string $type
	 * @param string $id
	 * @param array  $values
	 * @param array  $selected
	 * @param array  $attributes
	 *
	 * @return array
	 */
	protected function getChoices(string $type, string $id, array $values, array $selected, array $attributes): array
	{
		$choices = [];

		foreach ($values as $value => $label) {
			$checked   = in_array($value, $selected, false);
			$choices[] = [
				Tag::li(Tag::label([
					$this->$type($id, $value, $checked, $attributes),
					$label,
				])),
			];
		}

		return $choices;
	}

	/**
	 * Create a HTML reset input element.
	 *
	 * @param string $value
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function reset(string $value, array $attributes = []): string
	{
		return $this->input('reset', null, $value, $attributes);
	}

	/**
	 * Create a HTML image input element.
	 *
	 * @param string $id
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function image(string $id = '', array $attributes = []): string
	{
		return $this->input('image', $id, null, $attributes);
	}

	/**
	 * Create a submit button element.
	 *
	 * @param string $value
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function submit(string $value = null, array $attributes = []): string
	{
		return $this->input('submit', null, $value, $attributes);
	}

	/**
	 * Create a button element.
	 *
	 * @param string $value
	 * @param array  $attributes
	 *
	 * @return string
	 */
	public function button(string $value = null, array $attributes = []): string
	{
		if (!array_key_exists('type', $attributes)) {
			$attributes['type'] = 'button';
		}

		return Tag::button($attributes, $value);
	}

	/**
	 * Generate a hidden field with the current CSRF token.
	 *
	 * @param bool $referer
	 *
	 * @return string
	 */
	public function token(bool $referer = true): string
	{
		$token = $this->special('_wpnonce', $this->getToken());

		if ($referer) {
			$token .= $this->special('_wp_http_referer', wp_unslash($_SERVER['REQUEST_URI']));
		}

		return $token;
	}

	/**
	 * Parse the form action method.
	 *
	 * @param string $method
	 *
	 * @return string
	 */
	protected function getMethod(string $method): string
	{
		return $this->method = $method !== 'GET' ? 'POST' : 'GET';
	}

	/**
	 * Get the form action from the options.
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	protected function getAction(array $options): string
	{
		// We will send the form through the POST API, set the action name.
		$this->action = $options['action'] ?? $this->id();

		return admin_url('admin-post.php');
	}

	/**
	 * Get the form fields for the given method.
	 *
	 * @param string $method
	 *
	 * @return array
	 */
	protected function getHiddenFields(string $method): array
	{
		$fields = [];

		// If the HTTP method is in this list of spoofed methods, we will attach the
		// method spoofer hidden input to the form. This allows us to use regular
		// form to initiate PUT and DELETE requests in addition to the typical.
		if (in_array($method, static::$spoofedMethods, false)) {
			$fields[] = $this->special('_method', $method);
		}

		if ($method !== 'GET') {
			$fields[] = $this->token();
		}

		return $fields;
	}

	/**
	 * @param bool $plain
	 *
	 * @return string
	 */
	protected function getToken(bool $plain = false): string
	{
		$token = $this->id();

		return $plain ? $token : wp_create_nonce($token);
	}

	/**
	 * Get the id attribute for a field name.
	 *
	 * @param string $id
	 * @param array  $attributes
	 *
	 * @return string
	 */
	protected function getIdAttribute(string $id, array $attributes = []): string
	{
		if (array_key_exists('id', $attributes)) {
			return $attributes['id'];
		}

		$id = str_replace('.', '-', $id);

		if (Arr::get($attributes, 'multiple') && isset($attributes['type'])) {
			$id .= '-' . $attributes['value'];
		}

		return $id;
	}

	/**
	 * Get the name attribute for a field name.
	 *
	 * @param string $id
	 * @param string $tag
	 * @param array  $attributes
	 *
	 * @return string
	 */
	protected function getNameAttribute(string $id, string $tag, array $attributes = []): string
	{
		if (array_key_exists('name', $attributes)) {
			return $attributes['name'];
		}

		$name = $this->getNameFromId($this->id() . '.' . $id);

		// Add [] to <select> or <input type="checkbox"> with the "multiple" attribute
		if (Arr::get($attributes, 'multiple') && ($tag === 'select' || Arr::get($attributes, 'type') === 'checkbox')) {
			$name .= '[]';
		}

		return $name;
	}

	/**
	 * @param string $id
	 *
	 * @return string
	 */
	protected function getNameFromId(string $id): string
	{
		if (strpos($id, '.') === false || strpos($id, '[') !== false) {
			return $id;
		}

		$parts = explode('.', $id);
		$name  = array_shift($parts);

		if (!empty($parts)) {
			$name .= '[' . implode('][', $parts) . ']';
		}

		return $name;
	}

	/**
	 * Get the value that should be assigned to the field.
	 *
	 * @param string     $id
	 * @param string|int $value
	 *
	 * @return mixed
	 */
	protected function getValueAttribute(string $id, $value = null)
	{
		if ($id === null) {
			return $value;
		}

		if (($session = $this->getSessionValue($id)) !== null) {
			return $session;
		}

		if (($option = $this->getModelValue($id)) !== null) {
			return $option;
		}

		return $value;
	}

	/**
	 * @param string $id
	 *
	 * @return mixed|null
	 */
	protected function getModelValue(string $id)
	{
		return (empty($this->model) || !isset($this->model[$id])) ? null : $this->model[$id];
	}

	/**
	 * @param string $id
	 *
	 * @return mixed|null
	 */
	protected function getSessionValue($id)
	{
		return Input::getInstance()->get($id);
	}

	/**
	 * @return bool
	 */
	protected function isSessionEmpty(): bool
	{
		$input = Input::getInstance();

		if ($input->method() === 'GET') {
			return true;
		}

		return (bool) count($input->request());
	}

}
