<?php

namespace ic\Framework\Html;

use ic\Framework\Support\Arr;

/**
 * Class Tag
 *
 * @package ic\Framework\Html
 *
 * @method static a($attributes = [], $content = null)
 * @method static abbr($attributes = [], $content = null)
 * @method static area($attributes = [])
 * @method static article($attributes = [], $content = null)
 * @method static aside($attributes = [], $content = null)
 * @method static audio($attributes = [], $content = null)
 * @method static b($attributes = [], $content = null)
 * @method static base($attributes = [])
 * @method static bdi($attributes = [], $content = null)
 * @method static bdo($attributes = [], $content = null)
 * @method static blockquote($attributes = [], $content = null)
 * @method static body($attributes = [], $content = null)
 * @method static br($attributes = [])
 * @method static button($attributes = [], $content = null)
 * @method static canvas($attributes = [], $content = null)
 * @method static caption($attributes = [], $content = null)
 * @method static cite($attributes = [], $content = null)
 * @method static code($attributes = [], $content = null)
 * @method static col($attributes = [])
 * @method static colgroup($attributes = [], $content = null)
 * @method static command($attributes = [])
 * @method static datalist($attributes = [], $content = null)
 * @method static dd($attributes = [], $content = null)
 * @method static del($attributes = [], $content = null)
 * @method static details($attributes = [], $content = null)
 * @method static dfn($attributes = [], $content = null)
 * @method static div($attributes = [], $content = null)
 * @method static dl($attributes = [], $content = null)
 * @method static dt($attributes = [], $content = null)
 * @method static em($attributes = [], $content = null)
 * @method static embed($attributes = [])
 * @method static fieldset($attributes = [], $content = null)
 * @method static figcaption($attributes = [], $content = null)
 * @method static figure($attributes = [], $content = null)
 * @method static footer($attributes = [], $content = null)
 * @method static form($attributes = [], $content = null)
 * @method static h1($attributes = [], $content = null)
 * @method static h2($attributes = [], $content = null)
 * @method static h3($attributes = [], $content = null)
 * @method static h4($attributes = [], $content = null)
 * @method static h5($attributes = [], $content = null)
 * @method static h6($attributes = [], $content = null)
 * @method static head($attributes = [], $content = null)
 * @method static header($attributes = [], $content = null)
 * @method static hgroup($attributes = [], $content = null)
 * @method static hr($attributes = [])
 * @method static html($attributes = [], $content = null)
 * @method static i($attributes = [], $content = null)
 * @method static iframe($attributes = [], $content = null)
 * @method static img($attributes = [])
 * @method static input($attributes = [])
 * @method static ins($attributes = [], $content = null)
 * @method static kbd($attributes = [], $content = null)
 * @method static keygen($attributes = [])
 * @method static label($attributes = [], $content = null)
 * @method static legend($attributes = [], $content = null)
 * @method static li($attributes = [], $content = null)
 * @method static link($attributes = [])
 * @method static map($attributes = [], $content = null)
 * @method static mark($attributes = [], $content = null)
 * @method static menu($attributes = [], $content = null)
 * @method static meta($attributes = [])
 * @method static meter($attributes = [], $content = null)
 * @method static nav($attributes = [], $content = null)
 * @method static noscript($attributes = [], $content = null)
 * @method static object($attributes = [], $content = null)
 * @method static ol($attributes = [], $content = null)
 * @method static optgroup($attributes = [], $content = null)
 * @method static option($attributes = [], $content = null)
 * @method static output($attributes = [], $content = null)
 * @method static p($attributes = [], $content = null)
 * @method static param($attributes = [])
 * @method static pre($attributes = [], $content = null)
 * @method static progress($attributes = [], $content = null)
 * @method static q($attributes = [], $content = null)
 * @method static rp($attributes = [], $content = null)
 * @method static rt($attributes = [], $content = null)
 * @method static ruby($attributes = [], $content = null)
 * @method static s($attributes = [], $content = null)
 * @method static samp($attributes = [], $content = null)
 * @method static script($attributes = [], $content = null)
 * @method static section($attributes = [], $content = null)
 * @method static select($attributes = [], $content = null)
 * @method static small($attributes = [], $content = null)
 * @method static source($attributes = [])
 * @method static span($attributes = [], $content = null)
 * @method static strong($attributes = [], $content = null)
 * @method static style($attributes = [], $content = null)
 * @method static sub($attributes = [], $content = null)
 * @method static summary($attributes = [], $content = null)
 * @method static sup($attributes = [], $content = null)
 * @method static svg($attributes = [], $content = null)
 * @method static table($attributes = [], $content = null)
 * @method static tbody($attributes = [], $content = null)
 * @method static td($attributes = [], $content = null)
 * @method static textarea($attributes = [], $content = null)
 * @method static tfoot($attributes = [], $content = null)
 * @method static th($attributes = [], $content = null)
 * @method static thead($attributes = [], $content = null)
 * @method static time($attributes = [], $content = null)
 * @method static title($attributes = [], $content = null)
 * @method static tr($attributes = [], $content = null)
 * @method static track($attributes = [])
 * @method static u($attributes = [], $content = null)
 * @method static ul($attributes = [], $content = null)
 * @method static use ($attributes = [])
 * @method static var($attributes = [], $content = null)
 * @method static video($attributes = [], $content = null)
 * @method static wbr($attributes = [])
 */
class Html
{

    protected static $voidTags = [
        'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'use', 'wbr',
    ];

    protected static $urlAttributes = ['action', 'cite', 'data', 'formaction', 'href', 'src'];

    /**
     * @param string                          $tag
     * @param array|object                    $attributes
     * @param null|string|array|callable|bool $content
     *
     * @return string
     */
    public static function tag($tag, $attributes = [], $content = null)
    {
        $attributes = static::attributes($attributes);

        if (in_array($tag, static::$voidTags, false)) {
            if ($content === true) {
                return sprintf('<%s%s/>', $tag, $attributes);
            }

            return sprintf('<%s%s>', $tag, $attributes);
        }

        if (!is_string($content) && is_callable($content)) {
            $content = $content();
        }

        if (is_array($content)) {
            $content = Arr::implode($content, "\n");
        }

        return sprintf('<%1$s%2$s>%3$s</%1$s>', $tag, $attributes, $content);
    }

    /**
     * @param array|object $attributes
     *
     * @return string
     */
    public static function attributes($attributes)
    {
        if (is_object($attributes)) {
            $attributes = get_object_vars($attributes);
        }

        if (is_array($attributes)) {
            $attributes = implode(' ', array_map(function ($name) use ($attributes) {
                $value = $attributes[$name];

                if (is_bool($value) && !isset($attributes['type'])) {
                    return $value ? $name : '';
                }

                if ($value === '') {
                    return $value;
                }

                $value = in_array($name, static::$urlAttributes) ? esc_url($value) : esc_attr($value);

                return sprintf('%s="%s"', $name, $value);
            }, array_keys($attributes)));
        }

        if (empty($attributes)) {
            $attributes = '';
        } else {
            $attributes = ' ' . trim($attributes);
        }

        return $attributes;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return string
     */
    public static function __callStatic($name, $arguments)
    {
        array_unshift($arguments, $name);

        return call_user_func_array([__CLASS__, 'tag'], $arguments);
    }
}