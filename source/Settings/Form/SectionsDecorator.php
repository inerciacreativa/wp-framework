<?php

namespace ic\Framework\Settings\Form;

use ic\Framework\Settings\SettingsPage;

/**
 * Class SectionsDecorator
 *
 * @package ic\Framework\Settings\Form
 */
trait SectionsDecorator
{

	/**
	 * @var Sections
	 */
	protected $sections;

	/**
	 * Creates a new Sections object.
	 *
	 * @param SettingsPage $page
	 */
	public function createSections(SettingsPage $page): void
	{
		$this->sections = new Sections($page);
	}

	/**
	 * Adds a new section.
	 *
	 * @param string|null $id
	 * @param \Closure    $content
	 *
	 * @return Sections
	 */
	public function addSection($id, \Closure $content): Sections
	{
		return $this->sections->addSection($id, $content);
	}

	/**
	 * Sets the validation function.
	 *
	 * @param callable $validation
	 *
	 * @return Sections
	 */
	public function onValidation(callable $validation): Sections
	{
		return $this->sections->onValidation($validation);
	}

	/**
	 * Sets the finalization function.
	 *
	 * @param callable $finalization
	 *
	 * @return Sections
	 */
	public function onFinalization(callable $finalization): Sections
	{
		return $this->sections->onFinalization($finalization);
	}

	/**
	 * Adds an error.
	 *
	 * @param string $id
	 * @param string $message
	 *
	 * @return Sections
	 */
	public function addError(string $id, string $message): Sections
	{
		return $this->sections->addError($id, $message);
	}

	/**
	 * Registers the sections via Settings API.
	 */
	public function register(): void
	{
		$this->sections->register();
	}

}