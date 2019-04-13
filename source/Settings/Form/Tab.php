<?php

namespace ic\Framework\Settings\Form;

use ic\Framework\Html\Tag;
use ic\Framework\Settings\Page\PageTabbed;

/**
 * Class Tab
 *
 * @package ic\Framework\Settings\Form
 */
class Tab
{

	use SectionsDecorator;

	/**
	 * @var PageTabbed
	 */
	protected $page;

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var callable
	 */
	protected $content;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var bool
	 */
	protected $active = false;

	/**
	 * Tab constructor.
	 *
	 * @param PageTabbed $page
	 * @param string     $id
	 * @param string     $title
	 * @param callable   $content
	 */
	public function __construct(PageTabbed $page, string $id, string $title, callable $content = null)
	{
		$this->page    = $page;
		$this->id      = $id;
		$this->title   = $title;
		$this->content = $content;

		$this->sections($this->page);
	}

	/**
	 * Registers the sections via Settings API.
	 */
	public function register(): void
	{
		if ($this->content) {
			call_user_func($this->content, $this);
		}

		$this->sections->register();
		$this->active = true;
	}

	/**
	 * Retrieves the tab ID.
	 *
	 * @return string
	 */
	public function id(): string
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function url(): string
	{
		return add_query_arg([
			'page' => $this->page->id(),
			'tab'  => $this->id,
		], admin_url($this->page->parent()));
	}

	/**
	 * Retrieves the link to this tab.
	 *
	 * @return string
	 */
	public function link(): string
	{
		$link  = $this->url();
		$class = 'nav-tab';

		if ($this->active) {
			$class .= ' nav-tab-active';
		}

		return Tag::a([
			'href'  => $link,
			'class' => $class,
		], $this->title ?: $this->id);
	}

}