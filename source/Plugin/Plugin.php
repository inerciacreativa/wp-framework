<?php

namespace ic\Framework\Plugin;

use ic\Framework\Http\Input;
use ic\Framework\Html\Tag;

/**
 * Class Plugin
 *
 * @package ic\Framework\Plugin
 */
abstract class Plugin extends PluginBase
{

    /**
     * @inheritdoc
     */
    protected function onCreation()
    {
        parent::onCreation();

        $this->setHook()
             ->activation($this->getFileName(), function () {
                 if (($check = $this->dependencies()) !== true) {
                     $this->error($check);
                 }

                 $this->activate();
             })
             ->deactivation($this->getFileName(), function () {
                 $this->onUninstall();
             })
             ->on('plugins_loaded', function () {
                 if (($check = $this->dependencies()) !== true) {
                     $this->deactivate($check);
                 }
             });
    }

    /**
     * Check the Plugin dependencies.
     *
     * @return bool|string
     */
    protected function dependencies()
    {
        return true;
    }

    /**
     * Runs when the plugin is installed.
     *
     * You should use this method to perform actions that must be done on installation.
     */
    protected function onInstall()
    {
    }

    /**
     * Runs when the plugin is uninstalled.
     *
     * You should use this method to clean the house before the plugin is uninstalled.
     */
    protected function onUninstall()
    {
    }

    /**
     *
     */
    protected function setTranslation()
    {
        load_plugin_textdomain($this->id, false, $this->getRelativePath($this->languages));
    }

    /**
     * Runs when the plugin is activated.
     *
     * Executes install() and then flushes the rewrite rules.
     */
    private function activate()
    {
        $this->onInstall();

        flush_rewrite_rules();
    }

    /**
     * Deactivates the plugin if the dependencies are not satisfied.
     *
     * @param string|bool $error
     */
    private function deactivate($error)
    {
        if (!function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        deactivate_plugins(plugin_basename($this->getFileName()));

        $this->setHook()->on('admin_notices', function () use ($error) {
            $html = Tag::div(['class' => 'error'],
                Tag::p(Tag::strong(sprintf(__('%s has been deactivated.', 'ic-framework'), $this->name)))
            );

            if (is_string($error)) {
                $html->content(Tag::p($error));
            }

            echo $html;
        });
    }

    /**
     * Throws an error.
     *
     * If is in "scrape mode" (the plugin is being installed) then exists the script,
     * else it triggers a user error.
     *
     * @param string $message
     * @param int    $number
     */
    private function error($message, $number = E_USER_ERROR)
    {
        if (Input::getInstance()->query('action') === 'error_scrape') {
            exit(Tag::strong($message));
        }

        trigger_error($message, $number);
    }

}
