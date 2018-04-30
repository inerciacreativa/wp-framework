<?php

namespace ic\Framework\Hook;

/**
 * Class HookDecorator
 *
 * @package ic\Framework\Hook
 */
trait HookDecorator
{

	/**
	 * @return Hook
	 */
	protected function hook(): Hook
	{
		return Hook::bind($this);
	}

}