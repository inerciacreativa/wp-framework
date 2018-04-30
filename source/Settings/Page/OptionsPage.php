<?php

namespace ic\Framework\Settings\Page;

use ic\Framework\Settings\Form\SectionsDecorator;
use ic\Framework\Settings\Settings;
use ic\Framework\Settings\SettingsPage;
use ic\Framework\Support\Options;

/**
 * Class NativePage
 *
 * @package ic\Framework\Settings\Page
 */
class OptionsPage extends SettingsPage
{

	use SectionsDecorator;

	/**
	 * OptionsPage constructor.
	 *
	 * @param string  $id
	 * @param Options $options
	 */
	public function __construct(string $id, Options $options)
	{
		parent::__construct(Settings::OPTIONS, $id, $options);

		$this->createSections($this);

		$this->initialize();
	}

}