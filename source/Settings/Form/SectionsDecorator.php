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
    protected $sections = [];

    /**
     * Creates a new Sections object.
     *
     * @param SettingsPage $page
     */
    public function sections(SettingsPage $page)
    {
        $this->sections = new Sections($page);
    }

    /**
     * Adds a new section.
     *
     * @param string   $id
     * @param \Closure $content
     *
     * @return Sections
     */
    public function section($id, \Closure $content)
    {
        return $this->sections->section($id, $content);
    }

    /**
     * Sets the validation function.
     *
     * @param callable $validation
     *
     * @return Sections
     */
    public function validation(callable $validation)
    {
        return $this->sections->validation($validation);
    }

    //public function
    /**
     * Adds an error.
     *
     * @param string $id
     * @param string $message
     *
     * @return Sections
     */
    public function error($id, $message)
    {
        return $this->sections->error($id, $message);
    }

    /**
     * Registers the sections via Settings API.
     */
    public function register()
    {
        $this->sections->register();
    }

}