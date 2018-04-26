<?php

namespace ic\Framework\Support\Limiter;

use ic\Framework\Support\Str;

/**
 * Class LettersResolver
 *
 * @package ic\Framework\Support\Limiter
 */
class LettersResolver implements LimiterResolverInterface
{

    /**
     * @inheritdoc
     */
    public function count($string)
    {
        return Str::length($string);
    }

    /**
     * @inheritdoc
     */
    public function limit($string, $number)
    {
        return Str::substring($string, 0, Str::search($string, ' ', $number));
    }

}