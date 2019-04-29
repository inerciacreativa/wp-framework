<?php

namespace ic\Framework\Type;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class Type
 *
 * @package ic\Framework\Type
 */
abstract class Type
{

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $singular;

	/**
	 * @var string
	 */
	protected $plural;

	/**
	 * @var array
	 */
	protected $properties = [];

	/**
	 * @var
	 */
	protected $object;

	/**
	 * @var array
	 */
	protected static $public = ['name'];

	/**
	 * @param string $property
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function __call(string $property, $value)
	{
		$this->$property = $value[0];

		return $this;
	}

	/**
	 * Getter.
	 *
	 * @param string $property Property to get.
	 *
	 * @return mixed Property value.
	 *
	 * @throws InvalidArgumentException
	 */
	public function __get($property)
	{
		if (in_array($property, static::$public, false)) {
			return $this->$property;
		}

		if (is_object($this->object) && property_exists($this->object, $property)) {
			return $this->object->$property;
		}

		if (array_key_exists($property, $this->properties)) {
			return $this->properties[$property];
		}

		throw new InvalidArgumentException(sprintf('The property "%s" does not exists.', $property));
	}

	/**
	 * Setter.
	 *
	 * @param string $property
	 * @param mixed  $value
	 *
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 */
	public function __set($property, $value): void
	{
		if (is_object($this->object)) {
			throw new RuntimeException(sprintf('The object "%s" has been registered.', $this->name));
		}

		if (!array_key_exists($property, $this->properties)) {
			throw new InvalidArgumentException(sprintf('The property "%s" does not exists.', $property));
		}

		$this->properties[$property] = $value;
	}

	/**
	 * @param string $property
	 *
	 * @return bool
	 */
	public function __isset($property): bool
	{
		return in_array($property, static::$public, false) || (is_object($this->object) && property_exists($this->object, $property)) || array_key_exists($property, $this->properties);
	}

	/**
	 * @param string $singular
	 * @param string $plural
	 *
	 * @return $this
	 */
	public function nouns(string $singular, string $plural): self
	{
		$this->singular = $singular;
		$this->plural   = $plural;

		return $this;
	}

}
