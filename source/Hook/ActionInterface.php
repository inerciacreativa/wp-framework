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
	public function getId(): string;

	/**
	 * Returns the name of the hook.
	 *
	 * @return string
	 */
	public function getHook(): string;

	/**
	 * Whether the action is enabled.
	 *
	 * @return bool
	 */
	public function isEnabled(): bool;

	/**
	 * Enables the action.
	 */
	public function enable(): void;

	/**
	 * Disables the action.
	 */
	public function disable(): void;

}
