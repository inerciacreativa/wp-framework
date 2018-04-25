<?php

namespace ic\Framework\Support\Collection;

use ic\Framework\Support\Collection;
use ic\Framework\Support\Data;
use ic\Framework\Support\Arr;

/**
 * Class AdvancedCollection
 *
 * @package ic\Framework\Support\Collection
 */
class AdvancedCollection extends Collection
{

    /**
     * Get the sum of the given values.
     *
     * @param callable|string|null $callback
     *
     * @return mixed
     */
    public function sum($callback = null)
    {
        if ($callback === null) {
            return array_sum($this->items);
        }

        $callback = $this->valueRetriever($callback);

        return $this->reduce(function ($result, $item) use ($callback) {
            return $result + $callback($item);
        }, 0);
    }

    /**
     * Get the average value of a given key.
     *
     * @param callable|string|null $callback
     *
     * @return int
     */
    public function avg($callback = null)
    {
        if ($count = $this->count()) {
            return $this->sum($callback) / $count;
        }

        return 0;
    }

    /**
     * Alias for the "avg" method.
     *
     * @param callable|string|null $callback
     *
     * @return mixed
     */
    public function average($callback = null)
    {
        return $this->avg($callback);
    }

    /**
     * Get the median of a given key.
     *
     * @param null $key
     *
     * @return mixed|null
     */
    public function median($key = null)
    {
        $count = $this->count();
        if ($count === 0) {
            return null;
        }

        $collection = $key === null ? $this->pluck($key) : $this;
        $values     = $collection->sort()->values();
        $middle     = (int)($count / 2);

        if ($count % 2) {
            return $values->get($middle);
        }

        return (new static([
            $values->get($middle - 1), $values->get($middle),
        ]))->average();
    }

    /**
     * Get the mode of a given key.
     *
     * @param null $key
     *
     * @return array
     */
    public function mode($key = null)
    {
        $count = $this->count();
        if ($count === 0) {
            return null;
        }

        $collection = $key === null ? $this->pluck($key) : $this;
        $counts     = new self;

        $collection->each(function ($value) use ($counts) {
            $counts[$value] = isset($counts[$value]) ? $counts[$value] + 1 : 1;
        });

        $sorted       = $counts->sort();
        $highestValue = $sorted->last();

        return $sorted->filter(function ($value) use ($highestValue) {
            return $value === $highestValue;
        })->sort()->keys()->all();
    }

    /**
     * Get the max value of a given key.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function max($key = null)
    {
        return $this->reduce(function ($result, $item) use ($key) {
            $value = Data::get($item, $key);

            return $result === null || $value > $result ? $value : $result;
        });
    }

    /**
     * Get the min value of a given key.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function min($key = null)
    {
        return $this->reduce(function ($result, $item) use ($key) {
            $value = Data::get($item, $key);

            return $result === null || $value < $result ? $value : $result;
        });
    }

    /**
     * "Paginate" the collection by slicing it into a smaller collection.
     *
     * @param int $page
     * @param int $perPage
     *
     * @return static
     */
    public function forPage($page, $perPage)
    {
        return $this->slice(($page - 1) * $perPage, $perPage);
    }

    /**
     * Shuffle the items in the collection.
     *
     * @param int $seed
     *
     * @return static
     */
    public function shuffle($seed = null)
    {
        $items = $this->items;

        if ($seed === null) {
            shuffle($items);
        } else {
            mt_srand($seed);
            usort($items, function () {
                return mt_rand(-1, 1);
            });
        }

        return new static($items);
    }

    /**
     * Get one or more items randomly from the collection.
     *
     * @param int $amount
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function random($amount = 1)
    {
        if ($amount > ($count = $this->count())) {
            throw new \InvalidArgumentException("You requested {$amount} items, but there are only {$count} items in the collection");
        }

        $keys = array_rand($this->items, $amount);

        if ($amount === 1) {
            return $this->items[$keys];
        }

        return new static(array_intersect_key($this->items, array_flip($keys)));
    }

    /**
     * Key an associative array by a field or using a callback.
     *
     * @param callable|string $keyBy
     *
     * @return static
     */
    public function keyBy($keyBy)
    {
        $keyBy   = $this->valueRetriever($keyBy);
        $results = [];

        foreach ($this->items as $item) {
            $results[$keyBy($item)] = $item;
        }

        return new static($results);
    }

    /**
     * Group an associative array by a field or using a callback.
     *
     * @param callable|string $groupBy
     * @param bool            $preserveKeys
     *
     * @return static
     */
    public function groupBy($groupBy, $preserveKeys = false)
    {
        $groupBy = $this->valueRetriever($groupBy);
        $results = [];

        foreach ($this->items as $key => $value) {
            $groupKeys = $groupBy($value, $key);

            foreach ((array)$groupKeys as $groupKey) {
                if (!array_key_exists($groupKey, $results)) {
                    $results[$groupKey] = new static;
                }

                $results[$groupKey]->offsetSet($preserveKeys ? $key : null, $value);
            }
        }

        return new static($results);
    }

}