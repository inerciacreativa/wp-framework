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
	 * @var \DateTime
	 */
	protected $date;

	/**
	 * @var \DateTimeZone
	 */
	protected $timezone;

	/**
	 * @return Date
	 */
	public static function now(): Date
	{
		return new static();
		//return \DateTime::createFromFormat('Y-m-d H:i:s', current_time('mysql'));
	}

	/**
	 * @param $input
	 *
	 * @return Date
	 */
	public static function create(string $input): Date
	{
		return new static($input);
		//return \DateTime::createFromFormat('Y-m-d H:i:s', current_time('mysql'));
	}

	/**
	 * Date constructor.
	 *
	 * @param string $input
	 */
	public function __construct(string $input = 'now')
	{
		$this->date     = new \DateTime($input);
		$this->timezone = new \DateTimeZone(get_option('timezone_string'));

		$this->date->setTimezone($this->timezone);
	}

	/**
	 * @return \DateTime
	 */
	public function get(): \DateTime
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
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->format();
	}

	/**
	 * @param int|string $interval
	 *
	 * @return \DateInterval
	 *
	 * @throws \Exception
	 */
	public static function interval($interval): \DateInterval
	{
		if (is_numeric($interval)) {
			$interval = 'PT' . $interval . 'S';
		}

		return (new \DateTime())->add(new \DateInterval($interval))
		                        ->diff(new \DateTime());
	}

}