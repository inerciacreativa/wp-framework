<?php

namespace ic\Framework\Settings\Form;

use ic\Framework\Hook\HookDecorator;
use ic\Framework\Settings\Settings;
use ic\Framework\Settings\SettingsPage;
use ic\Framework\Support\Options;
use ic\Framework\Support\Arr;

/**
 * Class Sections
 *
 * @package ic\Framework\Settings\Form
 */
class Sections
{

    use HookDecorator;

    /**
     * @var SettingsPage
     */
    protected $page;

    /**
     * @var Tab
     */
    protected $tab;

    /**
     * @var Section[]
     */
    protected $sections = [];

    /**
     * @var callable
     */
    protected $validation;

    /**
     * @var callable
     */
    protected $done;

    /**
     * @var bool
     */
    protected $registered = false;

    /**
     * @var string
     */
    protected $default;

    /**
     * Sections constructor.
     *
     * @param SettingsPage $page
     * @param Tab          $tab
     */
    public function __construct(SettingsPage $page, Tab $tab = null)
    {
        $this->page    = $page;
        $this->tab     = $tab;
        $this->default = Settings::getDefaultSection($this->page->id());
    }

    /**
     * Adds a new section.
     *
     * @param string   $id
     * @param \Closure $content
     *
     * @return $this
     */
    public function section($id, \Closure $content)
    {
        $id = $id ?: $this->default;

        if (!isset($this->sections[$id])) {
            $this->sections[$id] = new Section($this->page, $id, $content);
        }

        return $this;
    }

    /**
     * Retrieves all fields in all sections.
     *
     * @return Field[]
     */
    public function fields()
    {
        $fields = [];

        foreach ($this->sections as $section) {
            $fields = array_merge($fields, $section->fields());
        }

        return $fields;
    }

    /**
     * Sets the validation function.
     *
     * @param callable $validation
     *
     * @return $this
     */
    public function validation(callable $validation)
    {
        $this->validation = $validation;

        return $this;
    }

    public function done(callable $done)
    {
        $this->done = $done;

        return $this;
    }

    /**
     * Registers the sections via Settings API.
     */
    public function register()
    {
        if ($this->registered) {
            return;
        }

        // If the page is "permalink" WP does not save values!
        if ($this->page->id() === 'permalink') {
            $this->setHook()->on('load-options-permalink.php', function () {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !check_admin_referer('update-permalink')) {
                    return;
                }

                $values = isset($_POST[$this->options()->id()]) ? $_POST[$this->options()->id()] : [];
                $values = $this->validate($values);

                $this->finish(null, $values);
            });
        } else {
            $this->setHook()->on('update_option_' . $this->options()->id(), 'finish', ['arguments' => 2]);

            register_setting($this->page->id(), $this->options()->id(), [
                'sanitize_callback' => function ($values) {
                    return $this->validate($values);
                },
            ]);
        }

        foreach ($this->sections as $section) {
            $section->register();
        }

        $this->registered = true;
    }

    /**
     * @return bool
     */
    public function registered()
    {
        return $this->registered;
    }

    /**
     * Adds an error.
     *
     * @param string $id
     * @param string $message
     *
     * @return $this
     */
    public function error($id, $message)
    {
        add_settings_error($this->page->id(), str_replace('.', '-', $id), $message);

        return $this;
    }

    /**
     * @return Options
     */
    protected function options()
    {
        return $this->page->options();
    }

    /**
     * Validate the values.
     *
     * @param $values
     *
     * @return array|mixed
     */
    protected function validate($values)
    {
        if (empty($values)) {
            $values = [];
        }

        $values = $this->normalize($values, $this->fields(), $this->options()->all());

        if (is_callable($this->validation)) {
            return call_user_func($this->validation, $values, $this);
        }

        return $values;
    }

    /**
     * Normalize the values. Correct the checkboxes values, and return the complete Options array.
     *
     * @param array   $values
     * @param Field[] $fields
     * @param array   $options
     *
     * @return array
     */
    protected function normalize(array $values, array $fields, array $options)
    {
        foreach ($fields as $field) {
            if ($field->type() === 'checkbox') {
                if (Arr::has($values, $field->id())) {
                    Arr::set($values, $field->id(), (bool)Arr::get($values, $field->id()));
                } else {
                    Arr::set($values, $field->id(), false);
                }
            }
        }

        return Arr::fill($options, $values);
    }

    protected function finish($old, $values)
    {
        $this->options()->fill($values);
        $this->options()->save();

        if (is_callable($this->done)) {
            call_user_func($this->done);
        }
    }

}