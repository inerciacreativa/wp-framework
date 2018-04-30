<?php

namespace ic\Framework\Settings\Page;

use ic\Framework\Html\Tag;
use ic\Framework\Settings\Form\Tabs;
use ic\Framework\Settings\SettingsPage;
use ic\Framework\Support\Options;

/**
 * Class CustomPage
 *
 * @package ic\Framework\Settings\Page
 */
abstract class CustomPage extends SettingsPage
{

	/**
	 * @var Tabs
	 */
	protected $tabs;

	/**
	 * @var string
	 */
	protected $capability = 'manage_options';

	/**
	 * CustomPage constructor.
	 *
	 * @param string  $parent
	 * @param string  $id
	 * @param Options $options
	 * @param string  $pageTitle
	 * @param string  $menuTitle
	 */
	public function __construct(string $parent, string $id, Options $options, string $pageTitle, string $menuTitle = '')
	{
		parent::__construct($parent, $id, $options, $pageTitle, $menuTitle ?: $pageTitle);

		$this->tabs = new Tabs($this);

		$this->initialize();
	}

	/**
	 * @inheritdoc
	 */
	protected function initialize(): void
	{
		parent::initialize();

		$this->hook()->before($this->getHook(), 'addMenu');
	}

	/**
	 * @inheritdoc
	 */
	public function register(): void
	{
		$this->tabs->register();
	}

	/**
	 * @param string $capability
	 *
	 * @return $this
	 */
	public function setCapability(string $capability): self
	{
		$this->capability = $capability;

		return $this;
	}

	/**
	 * Creates a new tab.
	 *
	 * @param string|null $id
	 * @param \Closure    $content
	 *
	 * @return $this
	 */
	public function addTab($id, \Closure $content): self
	{
		$this->tabs->addTab($id, $content);

		return $this;
	}

	/**
	 * @return string
	 */
	abstract protected function getHook(): string;

	/**
	 * Add the submenu page.
	 */
	protected function addMenu(): void
	{
		add_submenu_page($this->parent, $this->pageTitle, $this->menuTitle, $this->capability, $this->id, function () {
			echo Tag::div(['class' => 'wrap'], [
				Tag::h1($this->pageTitle),
				$this->capture(function () {
					settings_errors($this->id());
				}),
				$this->tabs->getNavigation(),
				$this->getForm()->open(['action' => $this->parent]),
				$this->capture(function () {
					do_settings_sections($this->id());
					submit_button();
				}),
				$this->getForm()->close(),
			]);
		});
	}

	/**
	 * @param callable $callback
	 *
	 * @return string
	 */
	protected function capture(callable $callback): string
	{
		ob_start();

		$callback();

		return ob_get_clean();
	}

}