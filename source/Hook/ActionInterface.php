<?php
namespace ic\Framework\Hook;

/**
 * Interface ActionInterface
 *
 * @package ic\Framework\Hook
 */
interface ActionInterface
{

    /**
     * Returns the action ID.
     *
     * @return string
     */
    public function getId();

    /**
     * Returns the name of the hook.
     *
     * @return string
     */
    public function getHook();

    /**
     * Whether the action is enabled.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Enables the action.
     *
     * @return $this
     */
    public function enable();

    /**
     * Disables the action.
     *
     * @return $this
     */
    public function disable();

}