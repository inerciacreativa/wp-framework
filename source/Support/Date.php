<?php

namespace ic\Framework\Support;

/**
 * Class Date
 *
 * @package ic\Framework\Support
 */
class Date
{

    /**
     * @param int|string $interval
     *
     * @return \DateInterval
     */
    public static function interval($interval)
    {
        if (is_numeric($interval)) {
            $interval = 'PT' . $interval . 'S';
        }

        return (new \DateTime())->add(new \DateInterval($interval))->diff(new \DateTime());
    }

    /**
     * @return \DateTime
     */
    public static function now()
    {
        return \DateTime::createFromFormat('Y-m-d H:i:s', current_time('mysql'));
    }

}