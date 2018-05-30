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
	public function count(string $string): int
	{
		return Str::length($string);
	}

	/**
	 * @inheritdoc
	 */
	public function limit(string $string, int $number): string
	{
		return Str::substring($string, 0, Str::search($string, ' ', $number));
	}

}