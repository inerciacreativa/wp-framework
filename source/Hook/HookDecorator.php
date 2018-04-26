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
    protected function setHook()
    {
        return Hook::bind($this);
    }

}