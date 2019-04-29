<?php

namespace ic\Framework\Api\Auth;

use ic\Framework\Api\Query;

/**
 * Interface OAuthInterface
 *
 * @package ic\Framework\Api\Auth
 */
interface AuthInterface
{

	/**
	 * @return string
	 */
	public function getId(): string;

	/**
	 * @return bool
	 */
	public function isReady(): bool;

	/**
	 * @param Query $query
	 *
	 * @return Query
	 */
	public function authorize(Query $query): Query;

	/**
	 * @return bool
	 */
	public function regenerate(): bool;

}