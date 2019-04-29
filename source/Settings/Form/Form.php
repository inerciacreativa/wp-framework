<?php

namespace ic\Framework\Settings\Form;

use ic\Framework\Data\Options;
use ic\Framework\Form\AdvancedForm;
use ic\Framework\Hook\Hookable;
use ic\Framework\Http\Input;

/**
 * Class Form
 *
 * @package ic\Framework\Settings\Form
 */
class Form extends AdvancedForm
{

	use Hookable;

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

	protected function initialize(): void
	{
		// The API does not save automatically the options when in network mode
		if (is_network_admin()) {
			$this->hook()->on('network_admin_edit_' . $this->id(), 'save');
		}
	}

	/**
	 * Save the options in network mode.
	 */
	protected function save(): void
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
	 * @see Form::save()
	 */
	protected function getAction(array $options): string
	{
		if (is_network_admin()) {
			// We'll redirect to the correct URL in the save() method.
			$page  = 'edit.php';
			$query = [
				'action' => $this->id(),
				'page'   => $options['action'] ?? 'settings.php',
			];

			return $this->getNetworkUrl($page, $query);
		}

		return $this->getSiteUrl('options.php');
	}

	/**
	 * @inheritdoc
	 */
	protected function getHiddenFields(string $method): array
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
	protected function getToken(bool $plain = false): string
	{
		$token = $this->id() . '-options';

		return $plain ? $token : wp_create_nonce($token);
	}

	/**
	 * Save the options.
	 */
	protected function saveValues(): void
	{
		$values = Input::getInstance()->get($this->id());
		$values = wp_unslash($values);

		$this->model->fill($values);
		$this->model->save();
	}

	/**
	 * Save the errors.
	 */
	protected function saveErrors(): void
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
	protected function getNetworkUrl(string $page, array $query = []): string
	{
		return add_query_arg(array_filter($query), network_admin_url($page));
	}

	/**
	 * @param string $page
	 *
	 * @return string
	 */
	protected function getSiteUrl(string $page): string
	{
		return admin_url($page);
	}

	/**
	 * @param string $url
	 */
	protected function redirect(string $url): void
	{
		wp_redirect($url);
		exit();
	}

}