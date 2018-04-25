<?php

namespace ic\Framework\Settings\Form;

use ic\Framework\Settings\SettingsPage;

/**
 * Interface SectionsInterface
 *
 * @package ic\Framework\Settings\Form
 */
interface SectionsInterface
{

    /**
     * Return the associated Page.
     *
     * @return \ic\Framework\Settings\SettingsPage
     */
    public function page();

    /**
     * Return all Sections.
     *
     * @return Sections
     */
    public function sections();

    /**
     * Add a new Section if not exists and/or return that Section.
     *
     * @param string          $id
     * @param string          $title
     * @param string|callable $content
     *
     * @return Section
     */
    public function section($id = null, $title = null, $content = null);

    /**
     * Return the current Section.
     *
     * @return Section
     */
    public function current();

    /**
     * Return all Fields.
     *
     * @return Field[]
     */
    public function fields();

    /**
     * Create a new Field and add it to the current Section.
     *
     * @param string          $id
     * @param string          $label
     * @param string|callable $callback
     * @param array           $attributes
     * @param mixed           $value
     * @param string|array    $chosen
     *
     * @return Section
     */
    public function field($id, $label, $callback, $attributes = [], $value = null, $chosen = null);

    /**
     * Add a validation callable.
     *
     * @param callable $validation
     *
     * @return SectionsInterface
     */
    public function validation(callable $validation);

    /**
     * Add a validation error.
     *
     * @param string $id
     * @param string $message
     */
    public function error($id, $message);

    /**
     * Register the sections with the Setting API.
     */
    public function register();

    /**
     * Whether the sections has been registered.
     *
     * @return bool
     */
    public function isRegistered();

}