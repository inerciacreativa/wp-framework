<?php

namespace ic\Framework\Settings\Form;

use ic\Framework\Html\Tag;
use ic\Framework\Http\Input;
use ic\Framework\Settings\Page\CustomPage;

/**
 * Class Tabs
 *
 * @package ic\Framework\Settings\Form
 */
class Tabs
{

	/**
	 * @var CustomPage
	 */
	protected $page;

	/**
	 * @var Tab[]
	 */
	protected $tabs = [];

	/**
	 * Tabs constructor.
	 *
	 * @param CustomPage $page
	 */
	public function __construct(CustomPage $page)
	{
		$this->page = $page;
	}

	/**
	 * Adds a new tab.
	 *
	 * @param string|null $id
	 * @param \Closure    $content
	 *
	 * @return $this
	 */
	public function addTab($id, \Closure $content): self
	{
		$id = $id ?: 'default';

		if (!isset($this->tabs[$id])) {
			$this->tabs[$id] = new Tab($this->page, $id, $content);
		}

		return $this;
	}

	/**
	 * Return the navigation breadcrumbs if there is more than one Tab.
	 *
	 * @return string
	 */
	public function getNavigation(): string
	{
		if (\count($this->tabs) < 2) {
			return '';
		}

		return Tag::h2(['class' => 'nav-tab-wrapper'], array_map(function (Tab $tab) {
			return $tab->getLink();
		}, $this->tabs));
	}

	/**
	 * Register the active tab.
	 */
	public function register(): void
	{
		if ($tab = $this->getCurrent()) {
			$tab->register();
		}
	}

	/**
	 * Get the current Tab in the admin.
	 *
	 * @return Tab|null
	 */
	protected function getCurrent(): ?Tab
	{
		$tab = null;

		if (empty($this->tabs)) {
			return $tab;
		}

		if (\count($this->tabs) > 1) {
			$input = Input::getInstance();
			$tab   = $input->query('tab');

			if (!$tab && $input->referer('page') === $this->page->id()) {
				$tab = $input->referer('tab');
			}
		}

		if (!$tab || !array_key_exists($tab, $this->tabs)) {
			$tab = reset($this->tabs)->getId();
		}

		return $this->tabs[$tab];
	}

}