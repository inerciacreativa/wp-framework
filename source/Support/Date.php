<?php

namespace ic\Framework\Support;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

/**
 * Class Date
 *
 * @package ic\Framework\Support
 */
class Date
{

	/**
	 * @var DateTime
	 */
	protected $date;

	/**
	 * @var DateTimeZone
	 */
	protected $timezone;

	/**
	 * @return static
	 *
	 * @throws InvalidArgumentException
	 */
	public static function now(): Date
	{
		return new static();
	}

	/**
	 * @param $input
	 *
	 * @return static
	 *
	 * @throws InvalidArgumentException
	 */
	public static function create(string $input): Date
	{
		return new static($input);
	}

	/**
	 * Date constructor.
	 *
	 * @param string $input
	 */

	/**
	 * Date constructor.
	 *
	 * @param string $input
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(string $input = 'now')
	{
		try {
			$this->date     = new DateTime($input);
			$this->timezone = new DateTimeZone(get_option('timezone_string'));

			$this->date->setTimezone($this->timezone);
		} catch (Exception $exception) {
			throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
		}
	}

	/**
	 * @return DateTime
	 */
	public function get(): DateTime
	{
		return $this->date;
	}

	/**
	 * @return int
	 */
	public function timestamp(): int
	{
		return $this->date->getTimestamp();
	}

	/**
	 * @param string $format
	 *
	 * @return string
	 */
	public function format(string $format = ''): string
	{
		$format = empty($format) ? (string) get_option('date_format') : $format;

		return date_i18n($format, $this->date->getTimestamp());
	}

	/**
	 * @param Date|DateTime $date
	 *
	 * @return DateInterval
	 *
	 * @throws InvalidArgumentException
	 */
	public function diff($date): DateInterval
	{
		if ($date instanceof self) {
			$date = $date->get();
		}

		if (!($date instanceof DateTime)) {
			throw new InvalidArgumentException('$date is not an instance of Date nor DateTime');
		}

		return $this->date->diff($date);
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->format();
	}

	/**
	 * @param int|string $interval
	 *
	 * @return DateInterval
	 *
	 * @throws Exception
	 */
	public static function interval($interval): DateInterval
	{
		if (is_numeric($interval)) {
			$interval = 'PT' . $interval . 'S';
		}

		return (new DateTime())->add(new DateInterval($interval))
							   ->diff(new DateTime());
	}

}
