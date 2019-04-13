<?php

namespace ic\Framework\Settings\Form;

use Closure;
use ic\Framework\Settings\Page\Page;

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
	 * @param Page $page
	 */
	protected function sections(Page $page): void
	{
		$this->sections = new Sections($page);
	}

	/**
	 * Adds a new section.
	 *
	 * @param string|null $id
	 * @param Closure     $content
	 *
	 * @return Sections
	 */
	public function section($id, Closure $content): Sections
	{
		return $this->sections->section($id, $content);
	}

	/**
	 * Adds an error.
	 *
	 * @param string $id
	 * @param string $message
	 *
	 * @return Sections
	 */
	public function error(string $id, string $message): Sections
	{
		return $this->sections->error($id, $message);
	}

	/**
	 * Sets the validation function.
	 *
	 * @param callable $validation
	 *
	 * @return Sections
	 */
	public function validation(callable $validation): Sections
	{
		return $this->sections->validation($validation);
	}

	/**
	 * Sets the finalization function.
	 *
	 * @param callable $finalization
	 *
	 * @return Sections
	 */
	public function finalization(callable $finalization): Sections
	{
		return $this->sections->finalization($finalization);
	}

}