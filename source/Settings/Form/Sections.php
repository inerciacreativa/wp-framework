<?php

namespace ic\Framework\Settings\Form;

use ic\Framework\Hook\HookDecorator;
use ic\Framework\Settings\Settings;
use ic\Framework\Settings\SettingsPage;
use ic\Framework\Support\Arr;
use ic\Framework\Support\Options;

/**
 * Class Sections
 *
 * @package ic\Framework\Settings\Form
 */
class Sections
{

	use HookDecorator;

	/**
	 * @var SettingsPage
	 */
	protected $page;

	/**
	 * @var Tab
	 */
	protected $tab;

	/**
	 * @var Section[]
	 */
	protected $sections = [];

	/**
	 * @var callable
	 */
	protected $validation;

	/**
	 * @var callable
	 */
	protected $finalization;

	/**
	 * @var bool
	 */
	protected $registered = false;

	/**
	 * @var string
	 */
	protected $default;

	/**
	 * Sections constructor.
	 *
	 * @param SettingsPage $page
	 * @param Tab          $tab
	 */
	public function __construct(SettingsPage $page, Tab $tab = null)
	{
		$this->page    = $page;
		$this->tab     = $tab;
		$this->default = Settings::getDefaultSection($this->page->id());
	}

	/**
	 * Adds a new section.
	 *
	 * @param string|null $id
	 * @param \Closure    $content
	 *
	 * @return $this
	 */
	public function addSection($id, \Closure $content): self
	{
		$id = $id ?: $this->default;

		if (!isset($this->sections[$id])) {
			$this->sections[$id] = new Section($this->page, $id, $content);
		}

		return $this;
	}

	/**
	 * Adds an error.
	 *
	 * @param string $id
	 * @param string $message
	 *
	 * @return $this
	 */
	public function addError(string $id, string $message): self
	{
		add_settings_error($this->page->id(), str_replace('.', '-', $id), $message);

		return $this;
	}

	/**
	 * Sets the validation function.
	 *
	 * @param callable $validation
	 *
	 * @return $this
	 */
	public function onValidation(callable $validation): self
	{
		$this->validation = $validation;

		return $this;
	}

	/**
	 * Sets the finalization function.
	 *
	 * @param callable $finalization
	 *
	 * @return $this
	 */
	public function onFinalization(callable $finalization): self
	{
		$this->finalization = $finalization;

		return $this;
	}

	/**
	 * Registers the sections via Settings API.
	 */
	public function register(): void
	{
		if ($this->registered) {
			return;
		}

		// If the page is "permalink" WP does not save values!
		if ($this->page->id() === 'permalink') {
			$this->hook()->on('load-options-permalink.php', function () {
				if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !check_admin_referer('update-permalink')) {
					return;
				}

				$values = $_POST[$this->getOptions()->id()] ?? [];
				$values = $this->validate($values);

				$this->finalize(null, $values);
			});
		} else {
			$this->hook()->on('update_option_' . $this->getOptions()
			                                          ->id(), 'finalize', ['arguments' => 2]);

			register_setting($this->page->id(), $this->getOptions()->id(), [
				'sanitize_callback' => function (array $values) {
					return $this->validate($values);
				},
			]);
		}

		foreach ($this->sections as $section) {
			$section->register();
		}

		$this->registered = true;
	}

	/**
	 * @return bool
	 */
	public function isRegistered(): bool
	{
		return $this->registered;
	}

	/**
	 * Retrieves all fields in all sections.
	 *
	 * @return Field[]
	 */
	protected function getFields(): array
	{
		$fields = [[]];

		foreach ($this->sections as $section) {
			$fields[] = $section->getFields();
		}

		return array_merge(...$fields);
	}

	/**
	 * @return Options
	 */
	protected function getOptions(): Options
	{
		return $this->page->getOptions();
	}

	/**
	 * Validate the values.
	 *
	 * @param $values
	 *
	 * @return array
	 */
	protected function validate(array $values): array
	{
		if (empty($values)) {
			$values = [];
		}

		$values = $this->normalize($values, $this->getFields());

		if (\is_callable($this->validation)) {
			return \call_user_func($this->validation, $values, $this);
		}

		return $values;
	}

	/**
	 * Normalize the values. Correct the checkboxes values, and return the
	 * complete Options array.
	 *
	 * @param array   $values
	 * @param Field[] $fields
	 *
	 * @return array
	 */
	protected function normalize(array $values, array $fields): array
	{
		foreach ($fields as $field) {
			if ($field->getType() === 'checkbox') {
				if (Arr::has($values, $field->getId())) {
					Arr::set($values, $field->getId(), (bool) Arr::get($values, $field->getId()));
				} else {
					Arr::set($values, $field->getId(), false);
				}
			}
		}

		return Arr::fill($this->getOptions()->all(), $values);
	}

	/**
	 * @param array|null $oldValues
	 * @param array      $newValues
	 */
	protected function finalize($oldValues, array $newValues): void
	{
		$this->getOptions()->fill($newValues);
		$this->getOptions()->save();

		if (\is_callable($this->finalization)) {
			\call_user_func($this->finalization, $newValues, $this);
		}
	}

}