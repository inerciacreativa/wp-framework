<?php

namespace ic\Framework\Support;

/**
 * Class Store
 *
 * @package ic\Framework\Support
 */
class Store implements \ArrayAccess, \Countable, \IteratorAggregate
{

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($this->items, $key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function add($key, $value)
    {
        if (!$this->has($key)) {
            $this->items = Arr::add($this->items, $key, $value);

            return true;
        }

        return false;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function set($key, $value)
    {
        if ($this->has($key) && $this->get($key) !== $value) {
            Arr::set($this->items, $key, $value);

            return true;
        }

        return false;
    }

    /**
     * Fill the options with an array or object values.
     *
     * @param array|object $values
     *
     * @return bool
     */
    public function fill($values)
    {
        $method = empty($this->items) ? 'add' : 'set';

        Arr::map(static::getValues($values), [$this, $method]);

        return true;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function forget($key)
    {
        if ($this->has($key)) {
            Arr::forget($this->items, $key);

            return true;
        }

        return false;
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key ?: $this->count(), $value);
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->forget($key);
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @param array|object $values
     *
     * @return array
     */
    protected static function getValues($values)
    {
        return Arr::dot(Arr::items($values));
    }

}