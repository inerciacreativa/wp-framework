<?php

namespace ic\Framework\Settings\Page;

use ic\Framework\Settings\Form\SectionsDecorator;
use ic\Framework\Support\Options;

/**
 * Class PageSimple
 *
 * @package ic\Framework\Settings\Page
 */
class PageSimple extends Page
{

	use SectionsDecorator;

	/**
	 * PageSimple constructor.
	 *
	 * @param string  $parent
	 * @param Options $options
	 * @param string  $title
	 * @param array   $config
	 */
	public function __construct(string $parent, Options $options, string $title, array $config = [])
	{
		parent::__construct($parent, $options, $title, $config);

		$this->sections($this);
	}

	/**
	 * @return string
	 */
	public function id(): string
	{
		// Check if it's an Options page.
		if ($id = $this->config('id')) {
			return $id;
		}

		return parent::id();
	}

	/**
	 * @inheritdoc
	 */
	protected function register(): void
	{
		$this->sections->register();
	}

	/**
	 * @return string
	 */
	protected function navigation(): string
	{
		return '';
	}

}