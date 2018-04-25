<?php

namespace ic\Framework\Support;

/**
 * Class Str
 *
 * @package ic\Framework\Support
 */
class Str
{

    /**
     * @var string
     */
    protected static $encoding = 'UTF-8';

    /**
     * @var bool
     */
    protected static $multiByte;

    /**
     * The cache of snake-cased words.
     *
     * @var array
     */
    protected static $snakeCache = [];

    /**
     * The cache of camel-cased words.
     *
     * @var array
     */
    protected static $camelCache = [];

    /**
     * The cache of studly-cased words.
     *
     * @var array
     */
    protected static $studlyCache = [];

    /**
     * Note: Adapted from Stringy\Stringy.
     *
     * @var array
     * @see https://github.com/danielstjules/Stringy/blob/2.3.1/LICENSE.txt
     */
    protected static $charsArray = [
        '0'    => ['°', '₀', '۰'],
        '1'    => ['¹', '₁', '۱'],
        '2'    => ['²', '₂', '۲'],
        '3'    => ['³', '₃', '۳'],
        '4'    => ['⁴', '₄', '۴', '٤'],
        '5'    => ['⁵', '₅', '۵', '٥'],
        '6'    => ['⁶', '₆', '۶', '٦'],
        '7'    => ['⁷', '₇', '۷'],
        '8'    => ['⁸', '₈', '۸'],
        '9'    => ['⁹', '₉', '۹'],
        'a'    => ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ā', 'ą', 'å', 'α', 'ά', 'ἀ', 'ἁ', 'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ', 'ἇ', 'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ', 'ᾆ', 'ᾇ', 'ὰ', 'ά', 'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а', 'أ', 'အ', 'ာ', 'ါ', 'ǻ', 'ǎ', 'ª', 'ა', 'अ', 'ا'],
        'b'    => ['б', 'β', 'Ъ', 'Ь', 'ب', 'ဗ', 'ბ'],
        'c'    => ['ç', 'ć', 'č', 'ĉ', 'ċ'],
        'd'    => ['ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ', 'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ'],
        'e'    => ['é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ', 'ἑ', 'ἒ', 'ἓ', 'ἔ', 'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э', 'є', 'ə', 'ဧ', 'ေ', 'ဲ', 'ე', 'ए', 'إ', 'ئ'],
        'f'    => ['ф', 'φ', 'ف', 'ƒ', 'ფ'],
        'g'    => ['ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ဂ', 'გ', 'گ'],
        'h'    => ['ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ'],
        'i'    => ['í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į', 'ı', 'ι', 'ί', 'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ', 'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ', 'ῑ', 'ῒ', 'ΐ', 'ῖ', 'ῗ', 'і', 'ї', 'и', 'ဣ', 'ိ', 'ီ', 'ည်', 'ǐ', 'ი', 'इ'],
        'j'    => ['ĵ', 'ј', 'Ј', 'ჯ', 'ج'],
        'k'    => ['ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ', 'ک'],
        'l'    => ['ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ'],
        'm'    => ['м', 'μ', 'م', 'မ', 'მ'],
        'n'    => ['ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န', 'ნ'],
        'o'    => ['ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő', 'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ', 'ὄ', 'ὅ', 'ὸ', 'ό', 'о', 'و', 'θ', 'ို', 'ǒ', 'ǿ', 'º', 'ო', 'ओ'],
        'p'    => ['п', 'π', 'ပ', 'პ', 'پ'],
        'q'    => ['ყ'],
        'r'    => ['ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ'],
        's'    => ['ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ', 'ſ', 'ს'],
        't'    => ['ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ', 'თ', 'ტ'],
        'u'    => ['ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'û', 'ū', 'ů', 'ű', 'ŭ', 'ų', 'µ', 'у', 'ဉ', 'ု', 'ူ', 'ǔ', 'ǖ', 'ǘ', 'ǚ', 'ǜ', 'უ', 'उ'],
        'v'    => ['в', 'ვ', 'ϐ'],
        'w'    => ['ŵ', 'ω', 'ώ', 'ဝ', 'ွ'],
        'x'    => ['χ', 'ξ'],
        'y'    => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ', 'ϋ', 'ύ', 'ΰ', 'ي', 'ယ'],
        'z'    => ['ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ'],
        'aa'   => ['ع', 'आ', 'آ'],
        'ae'   => ['ä', 'æ', 'ǽ'],
        'ai'   => ['ऐ'],
        'at'   => ['@'],
        'ch'   => ['ч', 'ჩ', 'ჭ', 'چ'],
        'dj'   => ['ђ', 'đ'],
        'dz'   => ['џ', 'ძ'],
        'ei'   => ['ऍ'],
        'gh'   => ['غ', 'ღ'],
        'ii'   => ['ई'],
        'ij'   => ['ĳ'],
        'kh'   => ['х', 'خ', 'ხ'],
        'lj'   => ['љ'],
        'nj'   => ['њ'],
        'oe'   => ['ö', 'œ', 'ؤ'],
        'oi'   => ['ऑ'],
        'oii'  => ['ऒ'],
        'ps'   => ['ψ'],
        'sh'   => ['ш', 'შ', 'ش'],
        'shch' => ['щ'],
        'ss'   => ['ß'],
        'sx'   => ['ŝ'],
        'th'   => ['þ', 'ϑ', 'ث', 'ذ', 'ظ'],
        'ts'   => ['ц', 'ც', 'წ'],
        'ue'   => ['ü'],
        'uu'   => ['ऊ'],
        'ya'   => ['я'],
        'yu'   => ['ю'],
        'zh'   => ['ж', 'ჟ', 'ژ'],
        '(c)'  => ['©'],
        'A'    => ['Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Å', 'Ā', 'Ą', 'Α', 'Ά', 'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ', 'Ἇ', 'ᾈ', 'ᾉ', 'ᾊ', 'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ', 'Ᾱ', 'Ὰ', 'Ά', 'ᾼ', 'А', 'Ǻ', 'Ǎ'],
        'B'    => ['Б', 'Β', 'ब'],
        'C'    => ['Ç', 'Ć', 'Č', 'Ĉ', 'Ċ'],
        'D'    => ['Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ'],
        'E'    => ['É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ', 'Ἑ', 'Ἒ', 'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э', 'Є', 'Ə'],
        'F'    => ['Ф', 'Φ'],
        'G'    => ['Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ'],
        'H'    => ['Η', 'Ή', 'Ħ'],
        'I'    => ['Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į', 'İ', 'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ', 'Ἷ', 'Ῐ', 'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї', 'Ǐ', 'ϒ'],
        'K'    => ['К', 'Κ'],
        'L'    => ['Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल'],
        'M'    => ['М', 'Μ'],
        'N'    => ['Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν'],
        'O'    => ['Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ø', 'Ō', 'Ő', 'Ŏ', 'Ο', 'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ', 'Ὸ', 'Ό', 'О', 'Θ', 'Ө', 'Ǒ', 'Ǿ'],
        'P'    => ['П', 'Π'],
        'R'    => ['Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ'],
        'S'    => ['Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ'],
        'T'    => ['Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ'],
        'U'    => ['Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'Û', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ų', 'У', 'Ǔ', 'Ǖ', 'Ǘ', 'Ǚ', 'Ǜ'],
        'V'    => ['В'],
        'W'    => ['Ω', 'Ώ', 'Ŵ'],
        'X'    => ['Χ', 'Ξ'],
        'Y'    => ['Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ', 'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ'],
        'Z'    => ['Ź', 'Ž', 'Ż', 'З', 'Ζ'],
        'AE'   => ['Ä', 'Æ', 'Ǽ'],
        'CH'   => ['Ч'],
        'DJ'   => ['Ђ'],
        'DZ'   => ['Џ'],
        'GX'   => ['Ĝ'],
        'HX'   => ['Ĥ'],
        'IJ'   => ['Ĳ'],
        'JX'   => ['Ĵ'],
        'KH'   => ['Х'],
        'LJ'   => ['Љ'],
        'NJ'   => ['Њ'],
        'OE'   => ['Ö', 'Œ'],
        'PS'   => ['Ψ'],
        'SH'   => ['Ш'],
        'SHCH' => ['Щ'],
        'SS'   => ['ẞ'],
        'TH'   => ['Þ'],
        'TS'   => ['Ц'],
        'UE'   => ['Ü'],
        'YA'   => ['Я'],
        'YU'   => ['Ю'],
        'ZH'   => ['Ж'],
        ' '    => ["\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81", "\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84", "\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87", "\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A", "\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80"],
    ];

    /**
     * @param $encoding
     *
     * @return bool
     */
    public static function setEncoding($encoding)
    {
        static::$encoding = $encoding;

        return static::isMultiByte();
    }

    /**
     * @return string
     */
    public static function getEncoding()
    {
        return static::$encoding;
    }

    /**
     * @return bool
     */
    public static function isMultiByte()
    {
        if (static::$multiByte === null) {
            static::$multiByte = false;

            if (extension_loaded('mbstring') && function_exists('mb_list_encodings')) {
                static::$multiByte = in_array(static::$encoding, mb_list_encodings(), false);
            }
        }

        return static::$multiByte;
    }

    /**
     * @param string $string
     * @param string $encoding
     *
     * @return string
     */
    public static function convert($string, $encoding)
    {
        if (static::isMultiByte() && !mb_detect_encoding($string, $encoding)) {
            return mb_convert_encoding($string, $encoding);
        }

        return $string;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function toUtf8($string)
    {
        return static::convert($string, 'UTF-8');
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function toEntities($string)
    {
        return static::convert($string, 'HTML-ENTITIES');
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function fromEntities($string)
    {
        return @html_entity_decode($string, ENT_QUOTES | ENT_HTML5, get_option('blog_charset'));
    }

    /**
     * @param $string
     *
     * @return string
     */
    public static function toAscii($string)
    {
        foreach (static::$charsArray as $key => $val) {
            $string = str_replace($val, $key, $string);
        }

        return preg_replace('/[^\x20-\x7E]/u', '', $string);
    }

    /**
     * @param string $string
     *
     * @return int
     */
    public static function length($string)
    {
        return static::isMultiByte() ? mb_strlen($string, static::getEncoding()) : strlen($string);
    }

    /**
     * @param string $string
     * @param string $needle
     * @param int    $offset
     *
     * @return bool|int
     */
    public static function search($string, $needle, $offset = 0)
    {
        return static::isMultiByte() ? mb_strpos($string, $needle, $offset, static::getEncoding()) : strpos($string, $needle, $offset);
    }

    /**
     * @param string $string
     * @param string $needle
     * @param int    $offset
     *
     * @return bool|int
     */
    public static function searchLast($string, $needle, $offset = 0)
    {
        return static::isMultiByte() ? mb_strrpos($string, $needle, $offset, static::getEncoding()) : strrpos($string, $needle, $offset);
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param string       $haystack
     * @param string|array $needles
     *
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if (static::search($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param string       $haystack
     * @param string|array $needles
     *
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if (static::search($haystack, $needle) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string       $haystack
     * @param string|array $needles
     *
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ((string)$needle === static::substring($haystack, -static::length($needle))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string   $string
     * @param int      $start
     * @param int|null $length
     *
     * @return string
     */
    public static function substring($string, $start, $length = null)
    {
        return static::isMultiByte() ? mb_substr($string, $start, $length, static::getEncoding()) : substr($string, $start, $length);
    }

    /**
     * @param string   $string
     * @param string   $replace
     * @param int      $start
     * @param int|null $length
     *
     * @return string
     */
    public static function replace($string, $replace, $start, $length = null)
    {
        $start  = (int)$start;
        $length = $length === null ? Str::length($string) : (int)$length;

        if ($start === 0) {
            return $replace . Str::substring($string, $length);
        }

        if ($start === Str::length($string)) {
            return $string . $replace;
        }

        return Str::substring($string, 0, $start) . $replace . Str::substring($string, $length);
    }

    /**
     * @param string $string
     * @param string $search
     * @param string $replace
     *
     * @return string
     */
    public static function replaceAll($string, $search, $replace)
    {
        return str_replace($search, $replace, $string);
    }

    /**
     * @param string $string
     * @param string $search
     * @param string $replace
     *
     * @return string
     */
    public static function replaceFirst($string, $search, $replace)
    {
        $position = static::search($string, $search);

        if ($position !== false) {
            return static::replace($string, $replace, $position, Str::length($search));
        }

        return $string;
    }

    /**
     * @param string $string
     * @param string $search
     * @param string $replace
     *
     * @return string
     */
    public static function replaceLast($string, $search, $replace)
    {
        $position = static::searchLast($string, $search);

        if ($position !== false) {
            return static::replace($string, $replace, $position, Str::length($search));
        }

        return $string;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function upper($string)
    {
        return static::isMultiByte() ? mb_strtoupper($string, static::getEncoding()) : strtoupper($string);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function lower($string)
    {
        return static::isMultiByte() ? mb_strtolower($string, static::getEncoding()) : strtolower($string);
    }

    /**
     * Convert a string to snake case.
     *
     * @param  string $value
     * @param  string $delimiter
     *
     * @return string
     */
    public static function snake($value, $delimiter = '_')
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', $value);
            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    /**
     * Convert a value to camel case.
     *
     * @param  string $value
     *
     * @return string
     */
    public static function camel($value)
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param  string $value
     *
     * @return string
     */
    public static function studly($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public static function toDotNotation($name)
    {
        if (strpos($name, '[') === false) {
            return $name;
        }

        return str_replace(['.', '[]', '[', ']'], ['_', '', '.', ''], $name);
    }

    /**
     * @param string $id
     *
     * @return string
     */
    public static function toArrayNotation($id)
    {
        if (strpos($id, '.') === false || strpos($id, '[' !== false)) {
            return $id;
        }

        $parts = explode('.', $id);
        $name  = array_shift($parts);

        if (!empty($parts)) {
            $name .= '[' . implode('][', $parts) . ']';
        }

        return $name;
    }

    /**
     * Cap a string with a single instance of a given value.
     *
     * @param string $string
     * @param string $cap
     *
     * @return string
     */
    public static function finish($string, $cap)
    {
        return preg_replace('/(?:' . preg_quote($cap, '/') . ')+$/u', '', $string) . $cap;
    }

    /**
     * Normalizes the whitespaces of a given string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function whitespace($string)
    {
        $string = preg_replace('/(\x{00A0})/iu', ' ', $string);

        return preg_replace('/\s+/u', ' ', $string);
    }

    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param string $title
     * @param string $separator
     *
     * @return string
     */
    public static function slug($title, $separator = '-')
    {
        $title = static::toAscii($title);

        $flip = $separator === '-' ? '_' : '-';

        $title = preg_replace('![' . preg_quote($flip, '/') . ']+!u', $separator, $title);
        $title = preg_replace('![^' . preg_quote($separator, '/') . '\pL\pN\s]+!u', '', mb_strtolower($title));
        $title = preg_replace('![' . preg_quote($separator, '/') . '\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

}