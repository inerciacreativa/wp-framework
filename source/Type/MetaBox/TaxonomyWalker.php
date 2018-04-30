<?php

namespace ic\Framework\Type\MetaBox;

use ic\Framework\Support\Arr;

/**
 * Class TaxonomyWalker
 *
 * @package ic\Framework\Form
 */
class TaxonomyWalker extends \Walker_Category_Checklist
{

    /**
     * @var TaxonomyMetaBox
     */
    protected $metaBox;

    /**
     * @var bool
     */
    protected $firstRun;

    /**
     * TaxonomyWalker constructor.
     *
     * @param TaxonomyMetaBox $metaBox
     */
    public function __construct(TaxonomyMetaBox $metaBox)
    {
        $this->metaBox  = $metaBox;
        $this->firstRun = true;
    }

    /**
     * @inheritdoc
     */
    public function walk($terms, $max_depth)
    {
        $arguments = func_get_arg(2);

        // The first run is for the selected terms.
        if (!$this->firstRun) {
            $terms = $this->metaBox->addNoneTerm($terms);
        } else {
            $this->firstRun = false;
        }

        return parent::walk($terms, $max_depth, $arguments);
    }

    /**
     * @inheritdoc
     */
    public function start_el(&$output, $term, $depth = 0, $arguments = [], $id = 0)
    {
        $popular  = Arr::get($arguments, 'popular_cats', []);
        $checked  = Arr::get($arguments, 'selected_cats', []);
        $disabled = (bool)Arr::get($arguments, 'disabled', false);
        $list     = (bool)Arr::get($arguments, 'list_only', false);
        $class    = \in_array($term->term_id, $popular, false) ? 'popular-category' : '';

        //"Press this" does not support custom taxonomies...
        if (!$list) {
            $input = $this->metaBox->getInput($term, [
                'class'    => $class,
                'checked'  => \in_array($term->term_id, $checked, false),
                'disabled' => $disabled,
            ]);

            $output .= $input->open();
        }
    }

}
