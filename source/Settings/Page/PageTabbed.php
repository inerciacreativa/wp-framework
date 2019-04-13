<?php

namespace ic\Framework\Settings\Page;

use ic\Framework\Html\Tag;
use ic\Framework\Http\Input;
use ic\Framework\Settings\Form\Tab;

/**
 * Class PageTabbed
 *
 * @package ic\Framework\Settings\Page
 */
class PageTabbed extends Page
{

	/**
	 * @var Tab[]
	 */
	protected $tabs = [];

	/**
	 * @inheritdoc
	 */
	protected function register(): void
	{
		if ($tab = $this->active()) {
			$tab->register();
		}
	}

	/**
	 * @param string        $id
	 * @param string|null   $title
	 * @param callable|null $content
	 *
	 * @return Tab
	 */
	public function tab(string $id, string $title = null, callable $content = null): Tab
	{
		if (isset($this->tabs[$id])) {
			return $this->tabs[$id];
		}

		return $this->tabs[$id] = new Tab($this, $id, $title, $content);
	}

	/**
	 * @return string
	 */
	protected function navigation(): string
	{
		if (count($this->tabs) < 2) {
			return '';
		}

		return Tag::h2(['class' => 'nav-tab-wrapper'], array_map(static function (Tab $tab) {
			return $tab->link();
		}, $this->tabs));
	}

	/**
	 * Get the current Tab in the admin.
	 *
	 * @return Tab|null
	 */
	protected function active(): ?Tab
	{
		$tab = null;

		if (empty($this->tabs)) {
			return $tab;
		}

		if (count($this->tabs) > 1) {
			$input = Input::getInstance();
			$tab   = $input->query('tab');

			if (!$tab && $input->referer('page') === $this->id()) {
				$tab = $input->referer('tab');
			}
		}

		if (!$tab || !array_key_exists($tab, $this->tabs)) {
			$tab = reset($this->tabs)->id();
		}

		return $this->tabs[$tab];
	}

}