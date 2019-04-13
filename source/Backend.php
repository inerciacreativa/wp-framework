<?php

namespace ic\Framework;

use ic\Framework\Html\Tag;
use ic\Framework\Plugin\PluginClass;
use ic\Framework\Settings\Form\Section;
use ic\Framework\Settings\Settings;

/**
 * Class Backend
 *
 * @package ic\Framework
 */
class Backend extends PluginClass
{

	/**
	 * @inheritdoc
	 */
	protected function initialize(): void
	{
		Settings::siteOptions($this->getOptions(), $this->name())
		        ->section('youtube', function (Section $section) {
			        $section->title(__('YouTube credentials', $this->id()))
			                ->description(sprintf(__('Required for using YouTube. Create a public API key in the %s and copy your API key.', $this->id()), Tag::a(['href' => 'https://console.developers.google.com/'], 'Google Developers Console')))
			                ->text('youtube.credentials.key', __('API Key', $this->id()), [
				                'class' => 'regular-text',
			                ]);
		        })
		        ->section('vimeo', function (Section $section) {
			        $section->title(__('Vimeo credentials', $this->id()))
			                ->description(sprintf(__('Required for using Vimeo. Register a new application in %s and copy your client identifier and secrets.', $this->id()), Tag::a(['href' => 'https://developer.vimeo.com/api/start'], 'Vimeo Developer')))
			                ->text('vimeo.credentials.id', __('Client Identifier', $this->id()), [
				                'class' => 'regular-text',
			                ])
			                ->text('vimeo.credentials.secret', __('Client Secrets', $this->id()), [
				                'class' => 'regular-text',
			                ]);
		        });
	}

}