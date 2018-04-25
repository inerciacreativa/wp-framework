<?php

namespace ic\Framework\Settings;

use ic\Framework\Form\AdvancedForm;
use ic\Framework\Hook\HookDecorator;
use ic\Framework\Http\Input;
use ic\Framework\Support\Options;

/**
 * Class Form
 *
 * @package ic\Framework\Settings\Form
 */
class SettingsForm extends AdvancedForm
{

    use HookDecorator;

    /**
     * Form constructor.
     *
     * @param Options $options
     */
    public function __construct(Options $options)
    {
        parent::__construct($options->id(), $options);

        $this->model = $options;

        $this->initialize();
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        // The API does not save automatically the options when in network mode
        if (is_network_admin()) {
            $this->setHook()->on('network_admin_edit_' . $this->id(), 'save');
        }
    }

    /**
     * Save the options in network mode.
     */
    protected function save()
    {
        check_admin_referer($this->getToken(true));

        // The requested values
        $this->saveValues();
        $this->saveErrors();

        // The promised redirection
        $input = Input::getInstance();
        $page  = $input->query('page');
        $query = [
            'page'             => $input->query('action'),
            'tab'              => $input->referer('tab'),
            'settings-updated' => 'true',
        ];

        $this->redirect($this->getNetworkUrl($page, $query));
    }

    /**
     * @inheritdoc
     *
     * @see SettingsForm::save()
     */
    protected function getAction(array $options)
    {
        if (is_network_admin()) {
            // We'll redirect to the correct URL in the save() method.
            $page  = 'edit.php';
            $query = [
                'action' => $this->id(),
                'page'   => isset($options['action']) ? $options['action'] : 'settings.php',
            ];

            return $this->getNetworkUrl($page, $query);
        }

        return $this->getSiteUrl('options.php');
    }

    /**
     * @inheritdoc
     */
    protected function getHiddenFields($method)
    {
        $fields = parent::getHiddenFields($method);

        if (!is_network_admin()) {
            $fields[] = $this->special('option_page', $this->id());
            $fields[] = $this->special('action', 'update');
        }

        return $fields;
    }

    /**
     * @inheritdoc
     */
    protected function getToken($plain = false)
    {
        $token = $this->id() . '-options';

        return $plain ? $token : wp_create_nonce($token);
    }

    /**
     * Save the options.
     */
    protected function saveValues()
    {
        $values = Input::getInstance()->get($this->id());
        $values = wp_unslash($values);

        $this->model->fill($values);
        $this->model->save();
    }

    /**
     * Save the errors.
     */
    protected function saveErrors()
    {
        if (!count(get_settings_errors())) {
            add_settings_error($this->id(), 'settings_updated', __('Settings saved.'), 'updated');
        }

        set_transient('settings_errors', get_settings_errors(), 30);
    }

    /**
     * @param string $page
     * @param array  $query
     *
     * @return string
     */
    protected function getNetworkUrl($page, array $query = [])
    {
        return add_query_arg(array_filter($query), network_admin_url($page));
    }

    /**
     * @param string $page
     *
     * @return string
     */
    protected function getSiteUrl($page)
    {
        return admin_url($page);
    }

    /**
     * @param string $url
     */
    protected function redirect($url)
    {
        wp_redirect($url);
        exit();
    }

}