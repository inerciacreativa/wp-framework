<?php

namespace ic\Framework\Support\Collection;

use ic\Framework\Support\Collection;

/**
 * Class TraversableCollection
 *
 * @package ic\Framework\Support\Collection
 */
class TraversableCollection extends Collection
{

    const NONE = 0;

    const FORWARD = 1;

    const BACKWARDS = -1;

    /**
     * @param      $key
     * @param bool $continueAtBottom
     *
     * @return mixed
     */
    public function next($key, $continueAtBottom = false)
    {
        return $this->sibling($key, self::FORWARD, $continueAtBottom);
    }

    /**
     * @param      $key
     * @param bool $continueAtTop
     *
     * @return mixed
     */
    public function previous($key, $continueAtTop = false)
    {
        return $this->sibling($key, self::BACKWARDS, $continueAtTop);
    }

    /**
     * @param      $key
     * @param int  $direction
     * @param bool $continueAtBounds
     *
     * @return mixed
     */
    public function sibling($key, $direction = self::FORWARD, $continueAtBounds = false)
    {
        $sibling = $this->keyOf($key, $direction, true, $continueAtBounds);

        return $this->get($sibling);
    }

    /**
     * @param int  $key
     * @param int  $direction
     * @param bool $checkBounds
     * @param bool $continueAtBounds
     *
     * @return string|null
     */
    public function keyOf($key, $direction = self::NONE, $checkBounds = false, $continueAtBounds = false)
    {
        $index = $this->indexOf($key, $direction, $checkBounds, $continueAtBounds);

        if ($index === null) {
            return null;
        }

        $keys = $this->keys();

        return isset($keys[$key]) ? $keys[$key] : null;
    }

    /**
     * @param string $key
     * @param int    $direction
     * @param bool   $checkBounds
     * @param bool   $continueAtBounds
     *
     * @return int|null
     */
    public function indexOf($key, $direction = self::NONE, $checkBounds = false, $continueAtBounds = false)
    {
        if (!$this->get($key)) {
            return null;
        }

        $index = array_search($key, $this->keys()->all(), false);

        if ($direction !== self::NONE) {
            $index = ($direction === self::BACKWARDS) ? --$index : ++$index;

            if ($continueAtBounds && $this->isOutOfBounds($index)) {
                $index = ($direction === self::BACKWARDS) ? ($this->count() - 1) : 0;
            }
        }

        if ($checkBounds && $this->isOutOfBounds($index)) {
            return null;
        }

        return $index;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    protected function isOutOfBounds($key)
    {
        return $key < 0 || $key >= $this->count();
    }

}