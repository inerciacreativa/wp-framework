<?php

namespace ic\Framework\Support;

/**
 * Class Options
 *
 * @package ic\Framework\Support
 */
class Options extends Store
{

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var int
	 */
	protected $network;

	/**
	 * @var bool
	 */
	protected $exists = false;

	/**
	 * Options constructor.
	 *
	 * @param string $id
	 * @param array  $defaults
	 * @param int    $network
	 * @param string $version
	 */
	public function __construct($id, array $defaults = [], $network = 0, $version = null)
	{
		$this->id      = $id;
		$this->network = is_multisite() ? absint($network) : 0;

		$this->fill($defaults);
		$this->load();
	}

	/**
	 * Return the id.
	 *
	 * @return string
	 */
	public function id(): string
	{
		return $this->id;
	}

	/**
	 * Return the network id.
	 *
	 * @return int
	 */
	public function network(): int
	{
		return $this->network;
	}

	/**
	 * Load the options from the database.
	 *
	 * @return $this
	 */
	public function load(): self
	{
		$this->exists = false;

		if ($this->network) {
			if (\function_exists('get_network_option')) {
				$values = get_network_option($this->network, $this->id, []);
			} else {
				$values = get_site_option($this->id, []);
			}
		} else {
			$values = get_option($this->id, []);
		}

		if (!empty($values)) {
			$this->fill($values);
			$this->exists = true;
		}

		return $this;
	}

	/**
	 * Save the options to the database.
	 *
	 * @return $this
	 */
	public function save(): self
	{
		if ($this->network) {
			if (\function_exists('update_network_option')) {
				update_network_option($this->network, $this->id, $this->all());
			} else {
				update_site_option($this->id, $this->all());
			}
		} else {
			update_option($this->id, $this->all());
		}

		$this->exists = true;

		return $this;
	}

	/**
	 * Whether the options exists in the database.
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		return $this->exists;
	}

}