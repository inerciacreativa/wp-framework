<?php

namespace ic\Framework\Form;

use ic\Framework\Support\Arr;
use ic\Framework\Html\Tag;
use ic\Framework\Support\Collection;

/**
 * Class AdvancedForm
 *
 * @package ic\Framework\Form
 */
class AdvancedForm extends Form
{

    /**
     * Create a choices field with all the image sizes.
     * By default uses select ($multiple and $expanded are set to false).
     *
     * @param string     $id
     * @param array|null $selected
     * @param array      $attributes {
     *
     * @type array       $parameters
     * @type string      $operator
     * @type array       $exclude
     * @type bool        $multiple
     * @type bool        $expanded
     *                               }
     *
     * @return string
     */
    public function image_sizes($id, $selected = null, array $attributes = [])
    {
        $values = $this->getImageSizes();

        return $this->choices($id, $values, $selected, $attributes);
    }

    /**
     * Create a choices field with all the post types.
     * By default uses checkboxes ($multiple and $expanded are set to true).
     *
     * @param string     $id
     * @param array|null $selected
     * @param array      $attributes {
     *
     * @type array       $parameters
     * @type string      $operator
     * @type array       $exclude
     * @type bool        $multiple
     * @type bool        $expanded
     *                               }
     *
     * @return string
     */
    public function post_types($id, $selected = null, array $attributes = [])
    {
        $attributes['multiple'] = Arr::get($attributes, 'multiple', true);
        $attributes['expanded'] = Arr::get($attributes, 'expanded', true);

        $values = $this->getObjectList('get_post_types', $attributes);

        return $this->choices($id, $values, $selected, $attributes);
    }

    /**
     * Create a choices field with all the taxonomies.
     * By default uses checkboxes ($multiple and $expanded are set to true).
     *
     * @param string     $id
     * @param array|null $selected
     * @param array      $attributes {
     *
     * @type array       $parameters
     * @type string      $operator
     * @type array       $exclude
     * @type bool        $multiple
     * @type bool        $expanded
     *                               }
     *
     * @return string
     */
    public function taxonomies($id, $selected = null, array $attributes = [])
    {
        $attributes['multiple'] = Arr::get($attributes, 'multiple', true);
        $attributes['expanded'] = Arr::get($attributes, 'expanded', true);

        $values = $this->getObjectList('get_taxonomies', $attributes);

        return $this->choices($id, $values, $selected, $attributes);
    }

    /**
     * @param string     $id
     * @param array|null $selected
     * @param array      $attributes
     *
     * @return string
     */
    public function terms($id, $selected = null, array $attributes = [])
    {
        $taxonomy = Arr::get($attributes, 'taxonomy', 'category');
        $multiple = Arr::get($attributes, 'multiple', true);
        $legend   = Arr::get($attributes, 'legend');

        $type = $multiple ? 'checkbox' : 'radio';

        $name = $this->getNameAttribute($id, null);
        $name = sprintf('%s[%s]', $name, $taxonomy);
        if ($multiple) {
            $name .= '[]';
        }

        $selected = $this->getValueAttribute($id, $selected);
        if (isset($selected[$taxonomy])) {
            $selected = $selected[$taxonomy];
        }

        $parameters = [
            'taxonomy'             => $taxonomy,
            'selected_cats'        => $selected,
            'popular_cats'         => false,
            'checked_ontop'        => true,
            'descendants_and_self' => 0,
            'walker'               => new TaxonomyWalker($name, $type),
            'echo'                 => false,
        ];

        $terms = wp_terms_checklist(0, $parameters);
        $field = Tag::fieldset();

        if ($legend) {
            $field->content(Tag::legend(['class' => 'screen-reader-text'], $legend));
        }

        $field->content(Tag::div(['class' => 'wp-tab-panel inside'], Tag::ul(['class' => 'categorychecklist'], $terms)));

        return $field;
    }

    /**
     * @param callable $callback
     * @param array    $attributes
     *
     * @return array
     */
    protected function getObjectList($callback, &$attributes)
    {
        $parameters = Arr::pull($attributes, 'parameters', ['public' => true]);
        $operator   = Arr::pull($attributes, 'operator', 'and');
        $exclude    = Arr::pull($attributes, 'exclude', []);

        $objects = $callback($parameters, 'objects', $operator);
        $values  = Arr::pluck($objects, 'label', 'name');
        $values  = Arr::except($values, $exclude);

        return $values;
    }

    /**
     * @return array
     */
    protected function getImageSizes()
    {
        $result = new Collection();
        $sizes  = get_intermediate_image_sizes();
        $custom = wp_get_additional_image_sizes();

        foreach ($sizes as $name) {
            if (isset($custom[$name])) {
                $dimension = ['width' => $custom[$name]['width'], 'height' => $custom[$name]['height']];
            } else {
                $dimension = ['width' => get_option("{$name}_size_w"), 'height' => get_option("{$name}_size_h")];
            }

            $result->put($name, $dimension);
        }

        return $result->sort(function ($a, $b) {
            $a = $a['width'] * ($a['height'] ?: 99999);
            $b = $b['width'] * ($b['height'] ?: 99999);

            if ($a === $b) {
                return 0;
            }

            return ($a > $b) ? -1 : 1;
        })->map(function ($dimension, $name) {
            return sprintf('%s (%dx%d)', $name, $dimension['width'], $dimension['height']);
        })->all();
    }

}