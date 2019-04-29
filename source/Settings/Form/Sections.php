<?php

namespace ic\Framework\Settings\Form;

use Closure;
use ic\Framework\Data\Options;
use ic\Framework\Hook\Hookable;
use ic\Framework\Http\Input;
use ic\Framework\Settings\Page\Page;
use ic\Framework\Settings\Settings;
use ic\Framework\Support\Arr;

/**
 * Class Sections
 *
 * @package ic\Framework\Settings\Form
 */
class Sections
{

	use Hookable;

	/**
	 * @var Page
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
	 * @var string
	 */
	protected $default;

	/**
	 * Sections constructor.
	 *
	 * @param Page $page
	 * @param Tab  $tab
	 */
	public function __construct(Page $page, Tab $tab = null)
	{
		$this->page    = $page;
		$this->tab     = $tab;
		$this->default = Settings::getDefaultSection($page->id());
	}

	/**
	 * Adds a new section.
	 *
	 * @param string|null $id
	 * @param Closure     $content
	 *
	 * @return $this
	 */
	public function section($id, Closure $content): self
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
	public function error(string $id, string $message): self
	{
		add_settings_error($this->id(), str_replace('.', '-', $id), $message);

		return $this;
	}

	/**
	 * Sets the validation function.
	 *
	 * @param callable $validation
	 *
	 * @return $this
	 */
	public function validation(callable $validation): self
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
	public function finalization(callable $finalization): self
	{
		$this->finalization = $finalization;

		return $this;
	}

	/**
	 * @return string
	 */
	public function id(): string
	{
		return $this->page->id();
	}

	/**
	 * Registers the sections via Settings API.
	 */
	public function register(): void
	{
		// If the page is "permalink" WP does not save values.
		// Handle the validation and finalization manually.
		if ($this->id() === 'permalink') {
			$this->hook()->on('load-options-permalink.php', function () {
				$input = Input::getInstance();

				if (!$input->isMethod('POST') || !check_admin_referer('update-permalink')) {
					return;
				}

				$values = $input->request($this->id(), []);
				$values = $this->validate($values);

				$this->finalize($this->options()->all(), $values, true);
			});
		} else {
			register_setting($this->id(), $this->id(), [
				'sanitize_callback' => function (array $values) {
					return $this->validate($values);
				},
			]);

			$this->hook()
			     ->on('update_option_' . $this->id(), 'finalize', ['arguments' => 2]);
		}

		foreach ($this->sections as $section) {
			$section->register();
		}
	}

	/**
	 * @return Options
	 */
	protected function options(): Options
	{
		return $this->page->options();
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
		$values = $this->normalize($values);

		if (is_callable($this->validation)) {
			return call_user_func($this->validation, $values, $this);
		}

		return $values;
	}

	/**
	 * @param array|null $oldValues
	 * @param array      $newValues
	 * @param bool       $save
	 */
	protected function finalize($oldValues, array $newValues, bool $save = false): void
	{
		$this->options()->fill($newValues);

		if ($save) {
			$this->options()->save();
		}

		if (is_callable($this->finalization)) {
			call_user_func($this->finalization, $newValues, $oldValues, $this);
		}
	}

	/**
	 * Normalize the values. Correct the checkboxes values, and return the
	 * complete Options array.
	 *
	 * @param array $newValues
	 *
	 * @return array
	 */
	protected function normalize(array $newValues): array
	{
		foreach ($this->fields() as $field) {
			if ($field->type() === 'checkbox') {
				if (Arr::has($newValues, $field->id())) {
					Arr::set($newValues, $field->id(), (bool) Arr::get($newValues, $field->id()));
				} else {
					Arr::set($newValues, $field->id(), false);
				}
			}
		}

		$oldValues = $this->options()->all();

		return Arr::merge($oldValues, $newValues);
	}

	/**
	 * Retrieves all fields in all sections.
	 *
	 * @return Field[]
	 */
	protected function fields(): array
	{
		$fields = [[]];

		foreach ($this->sections as $section) {
			$fields[] = $section->fields();
		}

		return array_merge(...$fields);
	}

}