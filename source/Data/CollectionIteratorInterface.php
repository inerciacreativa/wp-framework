<?php

namespace ic\Framework\Data;

use ArrayAccess;
use Countable;
use SeekableIterator;
use Serializable;

/**
 * Interface CollectionIteratorInterface
 *
 * @package ic\Framework\Data
 */
interface CollectionIteratorInterface extends ArrayAccess, Countable, SeekableIterator, Serializable
{

}