<?php

namespace ic\Framework;

use ic\Framework\Plugin\PluginClass;
use ic\Framework\Settings\Settings;
use ic\Framework\Settings\Form\Section;
use ic\Framework\Settings\Form\Tab;
use ic\Framework\Html\Tag;

/**
 * Class Backend
 *
 * @package ic\Framework
 */
class Backend extends PluginClass
{

    /**
     * @inheritdoc
     *            
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function initialize(): void
    {
        Settings::siteOptions($this->id(), $this->getOptions(), $this->name())
                ->addTab('video', function (Tab $tab) {
                    $tab
                        ->setTitle(__('Video Options', $this->id()))
                        ->addSection('youtube', function (Section $section) {
                            $section
                                ->title(__('YouTube credentials', $this->id()))
                                ->description(sprintf(
                                    __('Required for using YouTube. Create a public API key in the %s and copy your API key.'),
                                    Tag::a(['href' => 'https://console.developers.google.com/'], 'Google Developers Console')
                                ))
                                ->text('youtube.credentials.key', __('API Key', $this->id()), [
                                    'class' => 'regular-text',
                                ]);
                        })
                        ->addSection('vimeo', function (Section $section) {
                            $section
                                ->title(__('Vimeo credentials', $this->id()))
                                ->description(sprintf(
                                    __('Required for using Vimeo. Register a new application in %s and copy your client identifier and secrets.', $this->id()),
                                    Tag::a(['href' => 'https://developer.vimeo.com/api/start'], 'Vimeo Developer')
                                ))
                                ->text('vimeo.credentials.id', __('Client Identifier', $this->id()), [
                                    'class' => 'regular-text',
                                ])
                                ->text('vimeo.credentials.secret', __('Client Secrets', $this->id()), [
                                    'class' => 'regular-text',
                                ]);
                        });
                });
    }

}