<?php

namespace ic\Framework\Data;

/**
 * Class Options
 *
 * @package ic\Framework\Support
 *
 * @method Options add($key, $value = null)
 * @method Options set($key, $value = null)
 * @method Options forget($key)
 * @method Options fill($values)
 * @method Options merge(array $values)
 */
class Options extends Repository
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
	 * Options constructor.
	 *
	 * @param array  $defaults
	 * @param string $id
	 * @param int    $network
	 */
	public function __construct(array $defaults, string $id, int $network = 0)
	{
		parent::__construct($defaults);

		$this->id      = $id;
		$this->network = is_multisite() ? absint($network) : 0;

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
			$this->fill($values);
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
			if (function_exists('update_network_option')) {
				update_network_option($this->network, $this->id, $this->all());
			} else {
				update_site_option($this->id, $this->all());
			}
		} else {
			update_option($this->id, $this->all());
		}

		return $this;
	}

}