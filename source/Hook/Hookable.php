<?php

namespace ic\Framework\Hook;

/**
 * Trait Hookable
 *
 * @package ic\Framework\Hook
 */
trait Hookable
{

	/**
	 * @return Hook
	 */
	protected function hook(): Hook
	{
		return Hook::bind($this);
	}

}