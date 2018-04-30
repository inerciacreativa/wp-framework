<?php

namespace ic\Framework\Type;

use ic\Framework\Type\MetaBox\TaxonomyMetaBox;
use ic\Framework\Html\Tag;
use ic\Framework\Support\Arr;

/**
 * Class Taxonomy
 *
 * @package ic\Framework\Type
 *
 * @property-read string       $name
 * @property-read string|array $object_type           Name of the object type(s) for the taxonomy object
 * @property-read              $label                 A plural descriptive name for the taxonomy marked for translation.
 * @property array|\stdClass   $labels                An array of labels for this taxonomy.
 * @property string            $description           A short descriptive summary of what the taxonomy is.
 *
 * @property bool              $public                Controls how the taxonomy is visible to authors and readers.
 * @property bool              $publicly_queryable    Whether queries can be performed on the front end.
 * @property bool|string       $query_var             Sets the query_var key for this taxonomy.
 * @property bool              $has_archive           Enables taxonomy archives.
 * @property bool              $hierarchical          Whether this taxonomy can have descendants.
 * @property bool|array        $rewrite               Triggers the handling of rewrites for this taxonomy.
 * @property callable          $update_count_callback A function that will be called when the count of an associated object type is updated.
 *
 * @property bool              $show_ui               Whether to generate a default UI for managing this taxonomy.
 * @property bool|string       $show_in_menu          Where to show the taxonomy in the admin menu.
 * @property bool              $show_in_nav_menus     Whether is available for selection in navigation menus.
 * @property bool              $show_tagcloud         Whether to allow the Tag Cloud widget to use this taxonomy.
 * @property bool              $show_in_quick_edit    Whether to show the taxonomy in the quick/bulk edit panel.
 * @property bool              $show_admin_column     Whether to allow automatic creation of taxonomy columns on associated post types.
 * @property callable          $meta_box_cb           Provide a callback function name for the meta box display.
 *
 * @property array             $capabilities          An array of the capabilities for this taxonomy.
 * @property-read \stdClass    $cap                   An object with the capabilities for this taxonomy.
 *
 * @property bool              $show_in_rest          Whether to expose this taxonomy in the REST API.
 * @property string            $rest_base             The base slug that will be used when accessed using the REST API.
 * @property string            $rest_controller_class An optional custom controller to use instead of WP_REST_Terms_Controller.
 */
class Taxonomy extends Type
{

	/**
	 * @var array
	 */
	protected static $public = ['name', 'object_type', 'has_archive'];

	/**
	 * @var array
	 */
	protected $object_type;

	/**
	 * @var bool
	 */
	protected $has_archive = true;

	/**
	 * @var \WP_Taxonomy
	 */
	protected $taxonomy;

	/**
	 * @param string $name
	 * @param array  $object_type
	 *
	 * @return static
	 */
	public static function create($name, array $object_type = [])
	{
		return new static($name, $object_type);
	}

	/**
	 * Taxonomy constructor.
	 *
	 * @param string $name
	 * @param array  $object_type
	 */
	public function __construct($name, array $object_type = [])
	{
		$this->defaults();

		$this->name        = $name;
		$this->object_type = $object_type;

		add_action('init', function () {
			$this->register();
		}, 1);
	}

	/**
	 * @param bool $hierarchical
	 *
	 * @return $this
	 */
	public function hierarchical($hierarchical = true)
	{
		$this->hierarchical = (bool) $hierarchical;

		return $this;
	}

	/**
	 * @param bool $multiple
	 * @param bool $popular
	 * @param bool $none
	 *
	 * @return $this
	 */
	public function meta_box($popular = true, $multiple = true, $none = true)
	{
		$this->meta_box_cb = new TaxonomyMetaBox($this, $popular, $multiple, $none);

		return $this;
	}

	/**
	 * @param array $capabilities
	 *
	 * @return $this
	 */
	public function capabilities(array $capabilities = [])
	{
		$this->capabilities = array_merge([
			                                  'manage_terms' => 'manage_categories',
			                                  'edit_terms'   => 'manage_categories',
			                                  'delete_terms' => 'manage_categories',
			                                  'assign_terms' => 'edit_posts',
		                                  ], $capabilities);

		return $this;
	}

	/**
	 * @param string $slug
	 * @param bool   $front
	 * @param int    $endpoint
	 *
	 * @return $this
	 */
	public function rewrite($slug, $front = true, $endpoint = EP_NONE)
	{
		$this->rewrite = [
			'slug'         => sanitize_title_with_dashes($slug),
			'with_front'   => $front,
			'hierarchical' => $this->hierarchical,
			'ep_mask'      => $endpoint,
		];

		return $this;
	}

	/**
	 * @param array $labels
	 *
	 * @return $this
	 */
	public function labels(array $labels = [])
	{
		$singular = strtolower($this->singular);
		$plural   = strtolower($this->plural);

		$defaults = [
			'name'                       => $this->plural,
			'menu_name'                  => $this->plural,
			'singular_name'              => $this->singular,
			'search_items'               => sprintf(__('Search %s', 'ic-framework'), $plural),
			'all_items'                  => sprintf(__('All %s', 'ic-framework'), $plural),
			'edit_item'                  => sprintf(__('Edit %s', 'ic-framework'), $singular),
			'view_item'                  => sprintf(__('View %s', 'ic-framework'), $singular),
			'update_item'                => sprintf(__('Update %s', 'ic-framework'), $singular),
			'add_new_item'               => sprintf(__('Add New %s', 'ic-framework'), $singular),
			'new_item_name'              => sprintf(__('New %s Name', 'ic-framework'), $singular),
			'not_found'                  => sprintf(__('No %s found.', 'ic-framework'), $plural),
			'no_terms'                   => sprintf(__('No %s', 'ic-framework'), $plural),
			'items_list_navigation'      => sprintf(__('%s list navigation', 'ic-framework'), $plural),
			'items_list'                 => sprintf(__('%s list', 'ic-framework'), $plural),
			'parent_item'                => sprintf(__('Parent %s', 'ic-framework'), $this->singular),
			'parent_item_colon'          => sprintf(__('Parent %s:', 'ic-framework'), $this->singular),
			'popular_items'              => sprintf(__('Popular %s', 'ic-framework'), $this->plural),
			'separate_items_with_commas' => sprintf(__('Separate %s with commas', 'ic-framework'), $plural),
			'add_or_remove_items'        => sprintf(__('Add or remove %s', 'ic-framework'), $plural),
			'choose_from_most_used'      => sprintf(__('Choose from the most used %s', 'ic-framework'), $plural),
			'filter_items'               => sprintf(__('Filter by %s', 'ic-framework'), $plural),
		];

		$labels = array_merge($defaults, $labels);

		if ($this->properties['hierarchical']) {
			Arr::forget($labels, [
				'popular_items',
				'separate_items_with_commas',
				'add_or_remove_items',
				'choose_from_most_used',
			]);
		} else {
			Arr::forget($labels, ['parent_item', 'parent_item_colon']);
		}

		$this->labels = $labels;

		return $this;
	}

	/**
	 * @param bool $archive
	 *
	 * @return $this
	 */
	public function has_archive($archive = true)
	{
		$this->has_archive = (bool) $archive;

		return $this;
	}

	/**
	 * Return all the created series.
	 *
	 * @return array
	 */
	public function getAllTerms()
	{
		return $this->getTerms([
			                       'hide_empty' => false,
			                       'orderby'    => 'name',
		                       ]);
	}

	/**
	 * Retrieves the popular terms.
	 *
	 * @param int $number
	 *
	 * @return array
	 */
	public function getPopularTerms($number = 10)
	{
		return $this->getTerms([
			                       'orderby'      => 'count',
			                       'order'        => 'DESC',
			                       'number'       => $number,
			                       'hierarchical' => false,
		                       ]);
	}

	/**
	 * Retrieves the terms.
	 *
	 * @param array $arguments
	 *
	 * @uses get_terms()
	 *
	 * @return array|int
	 */
	public function getTerms(array $arguments = [])
	{
		$terms = get_terms($this->name, $arguments);

		return is_wp_error($terms) ? [] : $terms;
	}

	/**
	 * Retrieves the terms associated with the given post.
	 *
	 * @param int   $post_id
	 * @param array $arguments
	 *
	 * @uses wp_get_object_terms()
	 *
	 * @return array
	 */
	public function getPostTerms($post_id, array $arguments = [])
	{
		$terms = wp_get_object_terms($post_id, $this->name, $arguments);

		return is_wp_error($terms) ? [] : $terms;
	}

	/**
	 * Sets the default properties.
	 */
	protected function defaults()
	{
		$this->properties = [
			'labels'                => [],
			'description'           => '',
			'public'                => true,
			'publicly_queryable'    => null,
			'query_var'             => true,
			'hierarchical'          => false,
			'rewrite'               => true,
			'update_count_callback' => '',
			'show_ui'               => null,
			'show_in_menu'          => null,
			'show_in_nav_menus'     => null,
			'show_tagcloud'         => null,
			'show_in_quick_edit'    => null,
			'show_admin_column'     => true,
			'meta_box_cb'           => null,
			'capabilities'          => [],
			'show_in_rest'          => false,
			'rest_base'             => null,
			'rest_controller_class' => null,
		];
	}

	/**
	 * Set defaults and register the taxonomy.
	 */
	protected function register()
	{
		if (empty($this->properties['labels'])) {
			$this->labels();
		}

		if ($this->properties['rewrite'] === true) {
			$this->rewrite($this->name);
		}

		$object = register_taxonomy($this->name, $this->object_type, $this->properties);

		if (!is_wp_error($object)) {
			$this->initialize(get_taxonomy($this->name));
		}

		$this->properties = [];
	}

	/**
	 * @param \WP_Taxonomy $object
	 */
	protected function initialize($object)
	{
		$this->object = $object;

		if (is_admin()) {
			if ($this->object->show_admin_column) {
				add_action('restrict_manage_posts', function ($type) {
					$this->addPostsFilter($type);
				});
			}
		} else {
			if (!$this->has_archive) {
				add_action('pre_get_posts', function (\WP_Query $query) {
					if ($query->is_tax($this->name)) {
						$query->set_404();
					}
				});
			}
		}
	}

	/**
	 * Add the filter to the posts list.
	 *
	 * @param string $type
	 */
	protected function addPostsFilter($type)
	{
		if (!in_array($type, $this->object_type, false)) {
			return;
		}

		$terms   = $this->getAllTerms();
		$current = isset($_REQUEST[$this->name]) ? sanitize_text_field($_REQUEST[$this->name]) : '';

		if (empty($terms)) {
			return;
		}

		$label = Tag::label([
			                    'for'   => 'filter-by-' . $this->name,
			                    'class' => 'screen-reader-text',
		                    ], $this->object->labels->filter_items);

		$select = Tag::select([
			                      'id'   => 'filter-by-' . $this->name,
			                      'name' => $this->name,
		                      ], Tag::option(['value' => ''], $this->object->labels->all_items));

		foreach ($terms as $term) {
			$select->content(Tag::option([
				                             'value'    => $term->slug,
				                             'selected' => $current === $term->slug,
			                             ], sanitize_term_field('name', $term->name, $term->id, $this->name, 'display')));
		}

		echo $label, $select;
	}

}