<?php

namespace ic\Framework\Form;

use ic\Framework\Html\Tag;
use Walker_Category_Checklist;

/**
 * Class TaxonomyWalker
 *
 * @package ic\Framework\Form
 */
class TaxonomyWalker extends Walker_Category_Checklist
{

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * TaxonomyWalker constructor.
	 *
	 * @param string $name
	 * @param string $type
	 */
	public function __construct(string $name, string $type = 'checkbox')
	{
		$this->name = $name;
		$this->type = $type;
	}

	/**
	 * @inheritdoc
	 */
	public function start_el(&$output, $category, $depth = 0, $arguments = [], $id = 0): void
	{
		$attributes = [
			'name'    => $this->name,
			'type'    => $this->type,
			'value'   => $category->term_id,
			'checked' => in_array($category->term_id, $arguments['selected_cats'], false),
		];

		$output .= Tag::li(Tag::label([
			Tag::input($attributes),
			apply_filters('the_category', $category->name),
		]))->open();
	}

}
