<?php

namespace ic\Framework\Support;

use ic\Framework\Html\Document;

/**
 * Class Text
 *
 * @package ic\Framework\Support
 */
class Text
{

    const WHITESPACE = " \t\n\r\0\x0B";

    /**
     * @var string
     */
    private $text;

    /**
     * @param $text
     *
     * @return static
     */
    public static function make($text)
    {
        return new static($text);
    }

    /**
     * Text constructor.
     *
     * @param $text
     */
    public function __construct($text)
    {
        $this->text = Str::toUtf8($text);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return int
     */
    public function length()
    {
        return Str::length($this->text);
    }

    /**
     * @param string $needle
     * @param int    $offset
     *
     * @return bool|int
     */
    public function search($needle, $offset = 0)
    {
        return Str::search($this->text, $needle, $offset);
    }

    /**
     * @param string|array $needles
     *
     * @return bool
     */
    public function contains($needles)
    {
        return Str::contains($this->text, $needles);
    }

    /**
     * @param string|array $needles
     *
     * @return bool
     */
    public function startsWith($needles)
    {
        return Str::startsWith($this->text, $needles);
    }

    /**
     * @param string|array $needles
     *
     * @return bool
     */
    public function endsWith($needles)
    {
        return Str::endsWith($this->text, $needles);
    }

    /**
     * @return static
     */
    public function whitespace()
    {
        return new static(Str::whitespace($this->text));
    }

    /**
     * Strip whitespace (or other characters) from the beginning and end of the string.
     *
     * @param string $characters
     *
     * @return static
     */
    public function trim($characters = self::WHITESPACE)
    {
        return new static(trim($this->text, $characters));
    }

    /**
     * Strip whitespace (or other characters) from the beginning of the string.
     *
     * @param string $characters
     *
     * @return static
     */
    public function trimLeft($characters = self::WHITESPACE)
    {
        return new static(ltrim($this->text, $characters));
    }

    /**
     * Strip whitespace (or other characters) from the end of the string.
     *
     * @param string $characters
     *
     * @return static
     */
    public function trimRight($characters = self::WHITESPACE)
    {
        return new static(rtrim($this->text, $characters));
    }

    /**
     * Return part of the string.
     *
     * @param int      $start
     * @param int|null $length
     *
     * @return static
     */
    public function slice($start, $length = null)
    {
        $start  = (int)$start;
        $length = $length === null ? $this->length() : (int)$length;

        return new static(Str::substring($this->text, $start, $length));
    }

    /**
     * @param string $string
     * @param int    $position
     *
     * @return static
     */
    public function insert($string, $position)
    {
        return $this->replace($string, $position, 0);
    }

    /**
     * @param string $string
     *
     * @return static
     */
    public function before($string)
    {
        return $this->insert($string, 0);
    }

    /**
     * @param string $string
     *
     * @return static
     */
    public function after($string)
    {
        return $this->insert($string, $this->length());
    }

    /**
     * @param string   $replace
     * @param int      $start
     * @param int|null $length
     *
     * @return static
     */
    public function replace($replace, $start, $length = null)
    {
        return new static(Str::replace($this->text, Str::toUtf8($replace), $start, $length));
    }

    /**
     * @param string $search
     * @param string $replace
     *
     * @return static
     */
    public function replaceAll($search, $replace)
    {
        return new static(Str::replaceAll($this->text, $search, $replace));
    }

    /**
     * @param string $search
     * @param string $replace
     *
     * @return static
     */
    public function replaceFirst($search, $replace)
    {
        return new static(Str::replaceFirst($this->text, $search, $replace));
    }

    /**
     * @param string $search
     * @param string $replace
     *
     * @return static
     */
    public function replaceLast($search, $replace)
    {
        return new static(Str::replaceLast($this->text, $search, $replace));
    }

    /**
     * @param string $cap
     *
     * @return static
     */
    public function finish($cap)
    {
        return new static(Str::finish($this->text, Str::toUtf8($cap)));
    }

    /**
     * @param string $separator
     *
     * @return static
     */
    public function toSlug($separator = '-')
    {
        return new static(Str::slug($this->text, $separator));
    }

    /**
     * Make the string lowercase.
     *
     * @return static
     */
    public function toLowercase()
    {
        return new static(Str::lower($this->text));
    }

    /**
     * Make the string uppercase.
     *
     * @return static
     */
    public function toUppercase()
    {
        return new static(Str::upper($this->text));
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->text;
    }

    /**
     * @return Document
     */
    public function toDocument()
    {
        $dom = new Document();
        $dom->loadMarkup($this->whitespace()->toString());

        return $dom;
    }

    /**
     * Truncate the string to the number of words specified.
     *
     * @param int $number
     *
     * @return static
     */
    public function words($number)
    {
        return new static(TextLimiter::words($this->text, $number));
    }

    /**
     * Truncate the string to the number of letters specified.
     *
     * @param int $number
     *
     * @return static
     */
    public function letters($number)
    {
        return new static(TextLimiter::letters($this->text, $number));
    }

    /**
     * Strip HTML and PHP tags from the string.
     *
     * @param string $allowedTags
     *
     * @return static
     */
    public function stripTags($allowedTags = '')
    {
        return new static(strip_tags($this->text, $allowedTags));
    }

    /**
     * Balances the tags of the string.
     *
     * @return static
     */
    public function balanceTags()
    {
        return new static($this->toDocument()->saveMarkup());
    }

}