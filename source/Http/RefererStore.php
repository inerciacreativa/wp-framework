<?php
namespace ic\Framework\Http;

/**
 * Class RefererStore
 *
 * @package ic\Framework\Http
 */
class RefererStore extends InputStore
{

    public function __construct($referer)
    {
        $items = [];

        if (!empty($referer)) {
            $query = @parse_url($referer, PHP_URL_QUERY);

            if (!empty($query)) {
                $items = wp_parse_args($query);
            }
        }

        parent::__construct($items);
    }

}