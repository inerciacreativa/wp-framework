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

		foreach ($defaults as $key => $value) {
			$this->add($key, $value);
		}

		$this->load();
	}

	/**
	 * Return the id.
	 *
	 * @return string
	 */
	public function id()
	{
		return $this->id;
	}

	/**
	 * Return the network id.
	 *
	 * @return int
	 */
	public function network()
	{
		return $this->network;
	}

	/**
	 * Load the options from the database.
	 *
	 * @return $this
	 */
	public function load()
	{
		$this->exists = false;

		if ($this->network) {
			if (function_exists('get_network_option')) {
				$values = get_network_option($this->network, $this->id, []);
			} else {
				$values = get_site_option($this->id, []);
			}
		} else {
			$values = get_option($this->id, []);
		}

		if (!empty($values)) {
			$this->items = Arr::dot($values);
			$this->exists = true;
		}

		return $this;
	}

	/**
	 * Save the options to the database.
	 *
	 * @return $this
	 */
	public function save()
	{
		if ($this->network) {
			if (function_exists('update_network_option')) {
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
	public function exists()
	{
		return $this->exists;
	}

}