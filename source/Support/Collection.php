<?php

namespace ic\Framework\Support;

/**
 * Class Collection
 *
 * @package ic\Framework\Support
 */
class Collection implements \Countable, \ArrayAccess, \IteratorAggregate
{

	use MacroDecorator;

	/**
	 * @var array
	 */
	protected $items = [];

	/**
	 * Collection constructor.
	 *
	 * @param mixed $items
	 */
	public function __construct($items = [])
	{
		$this->items = Arr::items($items);
	}

	/**
	 * Create a new collection instance if the value isn't one already.
	 *
	 * @param mixed $items
	 *
	 * @return static
	 */
	public static function make($items = [])
	{
		if (empty($items)) {
			return new static;
		}

		if ($items instanceof self) {
			return $items;
		}

		return new static($items);
	}

	/**
	 * Get all of the items in the collection.
	 *
	 * @return array
	 */
	public function all(): array
	{
		return $this->items;
	}

	/**
	 * Collapse the collection of items into a single array.
	 *
	 * @return static
	 */
	public function collapse(): Collection
	{
		return new static(Arr::collapse($this->items));
	}

	/**
	 * Get a flattened array of the items in the collection.
	 *
	 * @param int $depth
	 *
	 * @return static
	 */
	public function flatten(int $depth = INF)
	{
		return new static(Arr::flatten($this->items, $depth));
	}

	/**
	 * Flip the items in the collection.
	 *
	 * @return static
	 */
	public function flip(): Collection
	{
		return new static(array_flip($this->items));
	}

	/**
	 * Concatenate values of a given key as a string.
	 *
	 * @param string $value
	 * @param string $glue
	 *
	 * @return string
	 */
	public function implode($value, $glue = null): string
	{
		$first = $this->first();

		if (is_array($first) || is_object($first)) {
			return implode($glue, $this->pluck($value)->all());
		}

		return implode($value, $this->items);
	}

	/**
	 * Count the number of items in the collection.
	 *
	 * @return int
	 */
	public function count(): int
	{
		return count($this->items);
	}

	/**
	 * @param mixed $key
	 *
	 * @return bool
	 */
	public function offsetExists($key): bool
	{
		return array_key_exists($key, $this->items);
	}

	/**
	 * Get an item at a given offset.
	 *
	 * @param mixed $key
	 *
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->items[$key];
	}

	/**
	 * Set the item at a given offset.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function offsetSet($key, $value): void
	{
		if ($key === null) {
			$this->items[] = $value;
		} else {
			$this->items[$key] = $value;
		}
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public function offsetUnset($key): void
	{
		unset($this->items[$key]);
	}

	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		if ($this->offsetExists($key)) {
			return $this->items[$key];
		}

		return Data::value($default);
	}

	/**
	 * Put an item in the collection at the specified index.
	 *
	 * @param int   $index
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function set(int $index, $value)
	{
		if (is_array($value)) {
			$this->items = array_slice($this->items, 0, $index) + $value + array_slice($this->items, $index);
		} else {
			array_splice($this->items, $index, 0, $value);
		}

		return $this;
	}

	/**
	 * Determine if an item exists in the collection by key.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has($key): bool
	{
		return $this->offsetExists($key);
	}

	/**
	 * Determine if an item exists in the collection.
	 *
	 * @param mixed $key
	 * @param mixed $operator
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function contains($key, $operator = null, $value = null): bool
	{
		if (func_num_args() === 1) {
			if ($this->useAsCallable($key)) {
				return $this->first($key) !== null;
			}

			return in_array($key, $this->items, false);
		}

		if (func_num_args() === 2) {
			$value    = $operator;
			$operator = '=';
		}

		return $this->contains($this->operatorForWhere($key, $operator, $value));
	}

	/**
	 * Determine if an item exists in the collection using strict comparison.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function containsStrict($key, $value = null): bool
	{
		if (func_num_args() === 2) {
			return $this->contains(function ($item) use ($key, $value) {
				return Data::get($item, $key) === $value;
			});
		}

		if ($this->useAsCallable($key)) {
			return $this->first($key) !== null;
		}

		return in_array($key, $this->items, true);
	}

	/**
	 * Determine if the collection is empty or not.
	 *
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return empty($this->items);
	}

	/**
	 * Remove an item from the collection by key.
	 *
	 * @param string|array $keys
	 *
	 * @return $this
	 */
	public function forget($keys): self
	{
		foreach ((array) $keys as $key) {
			$this->offsetUnset($key);
		}

		return $this;
	}

	/**
	 * Get and remove the first item from the collection.
	 *
	 * @return mixed|null
	 */
	public function shift()
	{
		return array_shift($this->items);
	}

	/**
	 * Push an item onto the beginning of the collection.
	 *
	 * @param mixed $value
	 * @param mixed $key
	 *
	 * @return $this
	 */
	public function prepend($value, $key = null): self
	{
		$this->items = Arr::prepend($this->items, $value, $key);

		return $this;
	}

	/**
	 * Push an item onto the end of the collection.
	 *
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function append($value): self
	{
		return $this->push($value);
	}

	/**
	 * Push an item onto the end of the collection.
	 *
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function push($value): self
	{
		$this->offsetSet(null, $value);

		return $this;
	}

	/**
	 * Put an item in the collection by key.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function put($key, $value): self
	{
		$this->offsetSet($key, $value);

		return $this;
	}

	/**
	 * Pulls an item from the collection.
	 *
	 * @param mixed $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function pull($key, $default = null)
	{
		return Arr::pull($this->items, $key, $default);
	}

	/**
	 * Get and remove the last item from the collection.
	 *
	 * @return mixed
	 */
	public function pop()
	{
		return array_pop($this->items);
	}

	/**
	 * Get the first item from the collection.
	 *
	 * @param callable $callback
	 * @param mixed    $default
	 *
	 * @return mixed
	 */
	public function first(callable $callback = null, $default = null)
	{
		if ($callback === null) {
			return count($this->items) > 0 ? reset($this->items) : Data::value($default);
		}

		return Arr::first($this->items, $callback, $default);
	}

	/**
	 * Get the last item from the collection.
	 *
	 * @param callable $callback
	 * @param mixed    $default
	 *
	 * @return mixed
	 */
	public function last(callable $callback = null, $default = null)
	{
		if ($callback === null) {
			return count($this->items) > 0 ? end($this->items) : Data::value($default);
		}

		return Arr::last($this->items, $callback, $default);
	}

	/**
	 * Get the values of a given key.
	 *
	 * @param string|array $value
	 * @param string|null  $key
	 *
	 * @return static
	 */
	public function pluck($value, string $key = null)
	{
		return new static(Arr::pluck($this->items, $value, $key));
	}

	/**
	 * Search the collection for a given value and return the corresponding key if successful.
	 *
	 * @param mixed $value
	 * @param bool  $strict
	 *
	 * @return mixed
	 */
	public function search($value, bool $strict = false)
	{
		if (!$this->useAsCallable($value)) {
			return array_search($value, $this->items, $strict);
		}

		foreach ($this->items as $key => $item) {
			if ($value($item, $key)) {
				return $key;
			}
		}

		return false;
	}

	/**
	 * Determine if all items in the collection pass the given test.
	 *
	 * @param string|callable $key
	 * @param mixed           $operator
	 * @param mixed           $value
	 *
	 * @return bool
	 */
	public function every($key, $operator = null, $value = null): bool
	{
		if (func_num_args() === 1) {
			$callback = $this->valueRetriever($key);

			foreach ($this->items as $k => $v) {
				if (!$callback($v, $k)) {
					return false;
				}
			}

			return true;
		}

		if (func_num_args() === 2) {
			$value    = $operator;
			$operator = '=';
		}

		return $this->every($this->operatorForWhere($key, $operator, $value));
	}

	/**
	 * Union the collection with the given items.
	 *
	 * @param mixed $items
	 *
	 * @return static
	 */
	public function union($items): Collection
	{
		return new static($this->items + Arr::items($items));
	}

	/**
	 * Merge the collection with the given items.
	 *
	 * @param static|array $items
	 *
	 * @return static
	 */
	public function merge($items): Collection
	{
		return new static(array_merge($this->items, Arr::items($items)));
	}

	/**
	 * Create a collection by using this collection for keys and another for its values.
	 *
	 * @param mixed $values
	 *
	 * @return static
	 */
	public function combine($values): Collection
	{
		return new static(array_combine($this->all(), Arr::items($values)));
	}

	/**
	 * Get the items in the collection that are not present in the given items.
	 *
	 * @param mixed $items
	 *
	 * @return static
	 */
	public function diff($items): Collection
	{
		return new static(array_diff($this->items, Arr::items($items)));
	}

	/**
	 * Get the items in the collection whose keys are not present in the given items.
	 *
	 * @param mixed $items
	 *
	 * @return static
	 */
	public function diffKeys($items): Collection
	{
		return new static(array_diff_key($this->items, Arr::items($items)));
	}

	/**
	 * Intersect the collection with the given items.
	 *
	 * @param mixed $items
	 *
	 * @return static
	 */
	public function intersect($items): Collection
	{
		return new static(array_intersect($this->items, Arr::items($items)));
	}

	/**
	 * Get all items except for those with the specified keys.
	 *
	 * @param mixed $keys
	 *
	 * @return static
	 */
	public function except($keys): Collection
	{
		$keys = is_array($keys) ? $keys : func_get_args();

		return new static(Arr::except($this->items, $keys));
	}

	/**
	 * Get the items with the specified keys.
	 *
	 * @param mixed $keys
	 *
	 * @return static
	 */
	public function only($keys): Collection
	{
		$keys = is_array($keys) ? $keys : func_get_args();

		return new static(Arr::only($this->items, $keys));
	}

	/**
	 * Sort through each item with a callback.
	 *
	 * @param callable|null $callback
	 *
	 * @return static
	 */
	public function sort(callable $callback = null): Collection
	{
		$items = $this->items;

		$callback ? uasort($items, $callback) : asort($items);

		return new static($items);
	}

	/**
	 * Sort the collection using the given callback.
	 *
	 * @param callable|string $callback
	 * @param int             $options
	 * @param bool            $descending
	 *
	 * @return static
	 */
	public function sortBy($callback, int $options = SORT_REGULAR, bool $descending = false): Collection
	{
		$results  = [];
		$callback = $this->valueRetriever($callback);
		// First we will loop through the items and get the comparator from a callback
		// function which we were given. Then, we will sort the returned values and
		// and grab the corresponding values for the sorted keys from this array.
		foreach ($this->items as $key => $value) {
			$results[$key] = $callback($value, $key);
		}

		$descending ? arsort($results, $options) : asort($results, $options);

		// Once we have sorted all of the keys in the array, we will loop through them
		// and grab the corresponding model so we can set the underlying items list
		// to the sorted version. Then we'll just return the collection instance.
		foreach (array_keys($results) as $key) {
			$results[$key] = $this->items[$key];
		}

		return new static($results);
	}

	/**
	 * Sort the collection in descending order using the given callback.
	 *
	 * @param callable|string $callback
	 * @param int             $options
	 *
	 * @return static
	 */
	public function sortByDesc($callback, int $options = SORT_REGULAR): Collection
	{
		return $this->sortBy($callback, $options, true);
	}

	/**
	 * Group an associative array by a field or using a callback.
	 *
	 * @param callable|string $groupBy
	 * @param bool            $preserveKeys
	 *
	 * @return static
	 */
	public function groupBy($groupBy, $preserveKeys = false): Collection
	{
		$groupBy = $this->valueRetriever($groupBy);
		$results = [];

		foreach ($this->items as $key => $value) {
			$groupKeys = $groupBy($value, $key);

			if (!is_array($groupKeys)) {
				$groupKeys = [$groupKeys];
			}

			foreach ($groupKeys as $groupKey) {
				if (!array_key_exists($groupKey, $results)) {
					$results[$groupKey] = new static;
				}

				$results[$groupKey]->offsetSet($preserveKeys ? $key : null, $value);
			}
		}

		return new static($results);
	}

	/**
	 * Key an associative array by a field or using a callback.
	 *
	 * @param callable|string $keyBy
	 *
	 * @return static
	 */
	public function keyBy($keyBy): Collection
	{
		$keyBy   = $this->valueRetriever($keyBy);
		$results = [];

		foreach ($this->items as $key => $item) {
			$resolvedKey = $keyBy($item, $key);

			if (is_object($resolvedKey)) {
				$resolvedKey = (string) $resolvedKey;
			}

			$results[$resolvedKey] = $item;
		}

		return new static($results);
	}

	/**
	 * Pass the collection to the given callback and return the result.
	 *
	 * @param callable $callback
	 *
	 * @return mixed
	 */
	public function pipe(callable $callback)
	{
		return $callback($this);
	}

	/**
	 * Execute a callback over each item.
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function each(callable $callback): self
	{
		foreach ($this->items as $key => $item) {
			if ($callback($item, $key) === false) {
				break;
			}
		}

		return $this;
	}

	/**
	 * Transform each item in the collection using a callback.
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function transform(callable $callback): self
	{
		$this->items = $this->map($callback)->all();

		return $this;
	}

	/**
	 * Pass the collection to the given callback and then return it.
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function tap(callable $callback): self
	{
		$callback(new static($this->items));

		return $this;
	}

	/**
	 * Run a map over each of the items.
	 *
	 * @param callable $callback
	 *
	 * @return static
	 */
	public function map(callable $callback): Collection
	{
		$keys  = array_keys($this->items);
		$items = array_map($callback, $this->items, $keys);

		return new static(array_combine($keys, $items));
	}

	/**
	 * Run a filter over each of the items.
	 *
	 * @param callable $callback
	 *
	 * @return static
	 */
	public function filter(callable $callback = null): Collection
	{
		if ($callback) {
			return new static(Arr::where($this->items, $callback));
		}

		return new static(array_filter($this->items));
	}

	/**
	 * Filter items by the given key value pair.
	 *
	 * @param string $key
	 * @param mixed  $operator
	 * @param mixed  $value
	 *
	 * @return static
	 */
	public function where(string $key, $operator, $value = null)
	{
		if (func_num_args() === 2) {
			$value    = $operator;
			$operator = '=';
		}

		return $this->filter($this->operatorForWhere($key, $operator, $value));
	}

	/**
	 * Filter items by the given key value pair using strict comparison.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return static
	 */
	public function whereStrict(string $key, $value): Collection
	{
		return $this->where($key, '===', $value);
	}

	/**
	 * Filter items by the given key value pair.
	 *
	 * @param string $key
	 * @param mixed  $values
	 * @param bool   $strict
	 *
	 * @return static
	 */
	public function whereIn(string $key, $values, bool $strict = false): Collection
	{
		$values = Arr::items($values);

		return $this->filter(function ($item) use ($key, $values, $strict) {
			return in_array(Data::get($item, $key), $values, $strict);
		});
	}

	/**
	 * Filter items by the given key value pair using strict comparison.
	 *
	 * @param string $key
	 * @param mixed  $values
	 *
	 * @return static
	 */
	public function whereInStrict(string $key, $values): Collection
	{
		return $this->whereIn($key, $values, true);
	}

	/**
	 * Create a collection of all elements that do not pass a given truth test.
	 *
	 * @param callable|mixed $callback
	 *
	 * @return static
	 */
	public function reject($callback): Collection
	{
		$useAsCallable = $this->useAsCallable($callback);

		return $this->filter(function ($value, $key) use ($callback, $useAsCallable) {
			return $useAsCallable ? !$callback($value, $key) : $value != $callback;
		});
	}

	/**
	 * Reverse items order.
	 *
	 * @param bool $preserveKeys
	 *
	 * @return static
	 */
	public function reverse(bool $preserveKeys = true): Collection
	{
		return new static(array_reverse($this->items, $preserveKeys));
	}

	/**
	 * Return only unique items from the collection array.
	 *
	 * @param string|callable|null $key
	 * @param bool                 $strict
	 *
	 * @return static
	 */
	public function unique($key = null, bool $strict = false): Collection
	{
		if ($key === null) {
			return new static(array_unique($this->items, SORT_REGULAR));
		}

		$callback = $this->valueRetriever($key);
		$exists   = [];

		return $this->reject(function ($item, $key) use ($callback, $strict, &$exists) {
			if (in_array($id = $callback($item, $key), $exists, $strict)) {
				return true;
			}

			$exists[] = $id;
		});
	}

	/**
	 * Splice portion of the underlying collection array.
	 *
	 * @param int   $offset
	 * @param int   $length
	 * @param mixed $replacement
	 *
	 * @return static
	 */
	public function splice(int $offset, int $length = null, $replacement = []): Collection
	{
		if (func_num_args() === 1) {
			return new static(array_splice($this->items, $offset));
		}

		return new static(array_splice($this->items, $offset, $length, $replacement));
	}

	/**
	 * Slice the underlying collection array.
	 *
	 * @param int  $offset
	 * @param int  $length
	 * @param bool $preserveKeys
	 *
	 * @return static
	 */
	public function slice(int $offset, int $length = null, $preserveKeys = true): Collection
	{
		return new static(array_slice($this->items, $offset, $length, $preserveKeys));
	}

	/**
	 * Take the first or last {$limit} items.
	 *
	 * @param int $limit
	 *
	 * @return static
	 */
	public function take(int $limit): Collection
	{
		if ($limit < 0) {
			return $this->slice($limit, abs($limit));
		}

		return $this->slice(0, $limit);
	}

	/**
	 * Chunk the underlying collection array.
	 *
	 * @param int $size
	 *
	 * @return static
	 */
	public function chunk(int $size): Collection
	{
		$chunks = [];

		foreach (array_chunk($this->items, $size, true) as $chunk) {
			$chunks[] = new static($chunk);
		}

		return new static($chunks);
	}

	/**
	 * Reduce the collection to a single value.
	 *
	 * @param callable $callback ($carry, $value, $key)
	 * @param mixed    $initial
	 *
	 * @return mixed
	 */
	public function reduce(callable $callback, $initial = null)
	{
		return Arr::reduce($this->items, $callback, $initial);
	}

	/**
	 * Get the keys of the collection items.
	 *
	 * @return static
	 */
	public function keys(): Collection
	{
		return new static(array_keys($this->items));
	}

	/**
	 * Reset the keys on the underlying array.
	 *
	 * @return static
	 */
	public function values(): Collection
	{
		return new static(array_values($this->items));
	}

	/**
	 * Get an iterator for the items.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->items);
	}

	/**
	 * Get the collection of items as a plain array.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return array_map(function ($value) {
			return method_exists($value, 'toArray') ? $value->toArray() : $value;
		}, $this->items);
	}

	/**
	 * Get the collection of items as JSON.
	 *
	 * @param int $options
	 *
	 * @return string
	 */
	public function toJson(int $options = 0): string
	{
		return json_encode($this->serialize(), $options);
	}

	/**
	 * Convert the object into something JSON serializable.
	 *
	 * @return array
	 */
	public function serialize(): array
	{
		return array_map(function ($value) {
			if (method_exists($value, 'serialize')) {
				return $value->serialize();
			}

			if (method_exists($value, 'toJson')) {
				return $value->toJson();
			}

			if (method_exists($value, 'toArray')) {
				return $value->toArray();
			}

			return $value;
		}, $this->items);
	}

	/**
	 * Convert the collection to its string representation.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->toJson();
	}

	/**
	 * Get a value retrieving callback.
	 *
	 * @param string $value
	 *
	 * @return callable
	 */
	protected function valueRetriever($value): callable
	{
		if ($this->useAsCallable($value)) {
			return $value;
		}

		return function ($item) use ($value) {
			return Data::get($item, $value);
		};
	}

	/**
	 * Determine if the given value is callable, but not a string.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	protected function useAsCallable($value): bool
	{
		return !is_string($value) && is_callable($value);
	}

	/**
	 * Get an operator checker callback.
	 *
	 * @param string $key
	 * @param string $operator
	 * @param mixed  $value
	 *
	 * @return \Closure
	 */
	protected function operatorForWhere(string $key, string $operator, $value): \Closure
	{
		return function ($item) use ($key, $operator, $value) {
			$retrieved = Data::get($item, $key);
			switch ($operator) {
				default:
				case '=':
				case '==':
					return $retrieved == $value;
				case '!=':
				case '<>':
					return $retrieved != $value;
				case '<':
					return $retrieved < $value;
				case '>':
					return $retrieved > $value;
				case '<=':
					return $retrieved <= $value;
				case '>=':
					return $retrieved >= $value;
				case '===':
					return $retrieved === $value;
				case '!==':
					return $retrieved !== $value;
			}
		};
	}
}