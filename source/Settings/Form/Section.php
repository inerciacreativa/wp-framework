<?php

namespace ic\Framework\Settings\Form;


use ic\Framework\Settings\SettingsPage;
use ic\Framework\Settings\Settings;

/**
 * Class Section
 *
 * @package ic\Framework\Settings\Form
 */
class Section
{

    use FieldsDecorator;

    /**
     * @var SettingsPage
     */
    protected $page;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string|callable
     */
    protected $description;

    /**
     * Section constructor.
     *
     * @param SettingsPage $page
     * @param string       $id
     * @param \Closure     $content
     */
    public function __construct(SettingsPage $page, string $id, \Closure $content)
    {
        $this->page = $page;
        $this->id   = $id;

        $content($this);
    }

    /**
     * Return the ID.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Return the page.
     *
     * @return SettingsPage
     */
    public function getPage(): SettingsPage
    {
        return $this->page;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string|callable $description
     *
     * @return $this
     */
    public function description($description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Register the section.
     *
     * @return $this
     */
    public function register(): self
    {
        // Register section only if custom or an option section that doesn't exists
        if (!Settings::isDefaultSection($this->getPage()->id(), $this->id)) {
            add_settings_section($this->id, $this->title, $this, $this->page->id());
        }

        foreach ($this->fields as $field) {
            $field->register();
        }

        return $this;
    }

    /**
     * Render the description for the section.
     */
    public function __invoke()
    {
        if (\is_callable($this->description)) {
            echo \call_user_func($this->description);
        }

        if (\is_string($this->description)) {
        	if (strpos($this->description, '<p>') === false) {
		        $this->description = "<p>$this->description</p>";
	        }

            echo $this->description;
        }
    }

}