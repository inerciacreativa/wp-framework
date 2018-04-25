<?php

namespace ic\Framework\Api\Auth;

use ic\Framework\Api\Query;

/**
 * Interface OAuthInterface
 *
 * @package ic\Framework\Api\OAuth
 */
interface AuthInterface
{

    /**
     * @return string
     */
    public function getId();

    /**
     * @return bool
     */
    public function isReady();

    /**
     * @param Query $query
     *
     * @return Query
     */
    public function authorize(Query $query);

    /**
     * @return bool
     */
    public function regenerate();

}