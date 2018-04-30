<?php

namespace ic\Framework\Type;

use ic\Framework\Html\Tag;
use ic\Framework\Support\Arr;

/**
 * Class PostType
 *
 * @package ic\Framework\Custom
 *
 * @property-read string  $name
 * @property-read string  $label                 A plural descriptive name for the post type marked for translation.
 * @property array        $labels                An array of labels for this post type.
 * @property string       $description           A short descriptive summary of what the post type is.
 *
 * @property bool         $public                Controls how the type is visible to authors and readers.
 * @property bool         $publicly_queryable    Whether queries can be performed on the front end.
 * @property bool|string  $query_var             Sets the query_var key for this post type.
 * @property bool         $exclude_from_search   Whether to exclude posts with this post type from front end search results.
 * @property bool|string  $has_archive           Enables post type archives.
 * @property bool         $hierarchical          Whether the post type is hierarchical.
 * @property bool|array   $rewrite               Triggers the handling of rewrites for this post type.
 * @property bool|array   $supports              Registers support of certain features.
 * @property array        $taxonomies            An array of registered taxonomies that will be used.
 *
 * @property bool         $show_ui               Whether to generate a default UI for managing this post type.
 * @property bool|string  $show_in_menu          Where to show the post type in the admin menu.
 * @property bool         $show_in_nav_menus     Whether is available for selection in navigation menus.
 * @property bool         $show_in_admin_bar     Whether to make this post type available in the admin bar.
 * @property int          $menu_position         The position in the menu order the post type should appear.
 * @property string       $menu_icon             The URL to the icon or the name of the icon from the iconfont.
 * @property callable     $register_meta_box_cb  A callback that will be called when setting up the meta boxes for the edit form.
 *
 * @property string|array $capability_type       The string to use to build the read, edit, and delete capabilities.
 * @property array        $capabilities          An array of the capabilities for this post type.
 * @property bool         $map_meta_cap          Whether to use the internal default meta capability handling.
 *
 * @property bool         $can_export            Can this post_type be exported.
 * @property bool         $delete_with_user      Whether to delete posts of this type when deleting a user.
 *
 * @property bool         $show_in_rest          Whether to expose this post type in the REST API.
 * @property string       $rest_base             The base slug that will be used when accessed using the REST API.
 * @property string       $rest_controller_class An optional custom controller to use instead of WP_REST_Posts_Controller.
 */
class PostType extends Type
{

	/**
	 * @var array
	 */
	protected $messages = [];

	/**
	 * @var callable
	 */
	protected $filter = false;

	/**
	 * @param string $name
	 * @param array  $taxonomies
	 *
	 * @return static
	 *
	 * @throws \RuntimeException
	 */
	public static function create(string $name, array $taxonomies = []): PostType
	{
		return new static($name, $taxonomies);
	}

	/**
	 * Custom post type constructor.
	 *
	 * @param string $name
	 * @param array  $taxonomies
	 *
	 * @throws \RuntimeException
	 */
	public function __construct(string $name, array $taxonomies = [])
	{
		$this->properties = $this->defaults();

		$this->name       = $name;
		$this->taxonomies = $taxonomies;

		add_action('init', function () {
			$this->register();
		}, 1);

		add_theme_support('post-thumbnails');
	}

	/**
	 * @param bool $hierarchical
	 *
	 * @return $this
	 */
	public function hierarchical(bool $hierarchical = true): self
	{
		$this->hierarchical = $hierarchical;

		return $this;
	}

	/**
	 * @param string $icon
	 * @param int    $position
	 *
	 * @return $this
	 */
	public function menu(string $icon, int $position = 20): self
	{
		$this->menu_icon     = $icon;
		$this->menu_position = $position;

		return $this;
	}

	/**
	 * @param string $slug
	 * @param bool   $front
	 * @param int    $endpoint
	 *
	 * @return $this
	 */
	public function rewrite(string $slug, bool $front = true, int $endpoint = EP_PERMALINK): self
	{
		$this->rewrite = [
			'slug'       => sanitize_title_with_dashes($slug),
			'with_front' => $front,
			'feeds'      => (bool) $this->has_archive,
			'pages'      => true,
			'ep_mask'    => $endpoint,
		];

		return $this;
	}

	/**
	 * @param bool|callable $filter
	 *
	 * @return $this
	 */
	public function filter_link($filter = true): self
	{
		$this->filter = false;

		if ($this->hierarchical) {
			return $this;
		}

		if (\is_callable($filter)) {
			$this->filter = $filter;
		} else if ($filter === true) {
			$this->filter = function ($link, \WP_Post $post) {
				if ($post->post_type !== $this->name) {
					return $link;
				}

				$date = explode(' ', date('Y m d', strtotime($post->post_date)));
				$date = [
					'%year%'     => $date[0],
					'%monthnum%' => $date[1],
					'%day%'      => $date[2],
				];

				return str_replace(array_keys($date), array_values($date), $link);
			};
		}

		return $this;
	}

	/**
	 * @param array $supports
	 *
	 * @return $this
	 */
	public function supports(array $supports = []): self
	{
		$defaults = [
			'title',
			'editor',
			'excerpt',
			'thumbnail',
			'author',
			'revisions',
			'comments',
			'trackbacks',
			'page-attributes',
			'custom-fields',
			'post-formats',
		];

		$this->supports = array_intersect($defaults, $supports);

		return $this;
	}

	/**
	 * @param array $labels
	 *
	 * @return $this
	 */
	public function labels(array $labels = []): self
	{
		$singular = strtolower($this->singular);
		$plural   = strtolower($this->plural);

		$defaults = [
			'name'                  => $this->plural,
			'menu_name'             => $this->plural,
			'singular_name'         => $this->singular,
			'name_admin_bar'        => $this->singular,
			'featured_image'        => __('Featured Image', 'ic-framework'),
			'set_featured_image'    => __('Set featured image', 'ic-framework'),
			'remove_featured_image' => __('Remove featured image', 'ic-framework'),
			'use_featured_image'    => __('Use as featured image', 'ic-framework'),
			'add_new'               => __('Add New', 'ic-framework'),
			'add_new_item'          => sprintf(__('Add New %s', 'ic-framework'), $singular),
			'new_item'              => sprintf(__('New %s', 'ic-framework'), $singular),
			'edit_item'             => sprintf(__('Edit %s', 'ic-framework'), $singular),
			'view_item'             => sprintf(__('View %s', 'ic-framework'), $singular),
			'all_items'             => sprintf(__('All %s', 'ic-framework'), $plural),
			'search_items'          => sprintf(__('Search %s', 'ic-framework'), $plural),
			'parent_item_colon'     => sprintf(__('Parent %s:', 'ic-framework'), $singular),
			'not_found'             => sprintf(__('No %s found.', 'ic-framework'), $plural),
			'not_found_in_trash'    => sprintf(__('No %s found in Trash.', 'ic-framework'), $plural),
			'archives'              => sprintf(__('%s archives', 'ic-framework'), $singular),
			'insert_into_item'      => sprintf(__('Insert into %s', 'ic-framework'), $singular),
			'uploaded_to_this_item' => sprintf(__('Uploaded to this %s', 'ic-framework'), $singular),
			'filter_items_list'     => sprintf(__('Filter %s list', 'ic-framework'), $plural),
			'items_list_navigation' => sprintf(__('%s list navigation', 'ic-framework'), $plural),
			'items_list'            => sprintf(__('%s list', 'ic-framework'), $plural),
		];

		$this->labels = array_merge($defaults, $labels);

		return $this;
	}

	/**
	 * @param array $messages
	 *
	 * @return $this
	 */
	public function messages(array $messages = []): self
	{
		$defaults = [
			0  => '',
			1  => sprintf(__('%s updated.', 'ic-framework'), $this->singular),
			2  => __('Custom field updated.', 'ic-framework'),
			3  => __('Custom field deleted.', 'ic-framework'),
			4  => sprintf(__('%s updated.', 'ic-framework'), $this->singular),
			5  => sprintf(__('%s restored to revision from %s', 'ic-framework'), $this->singular, '%s'),
			6  => sprintf(__('%s published.', 'ic-framework'), $this->singular),
			7  => sprintf(__('%s saved.', 'ic-framework'), $this->singular),
			8  => sprintf(__('%s submitted.', 'ic-framework'), $this->singular),
			9  => sprintf(__('%s scheduled for: <strong>%s</strong>.', 'ic-framework'), $this->singular, '%s'),
			10 => sprintf(__('%s draft updated.', 'ic-framework'), $this->singular),
		];

		$this->messages = array_merge($defaults, $messages);

		return $this;
	}

	/**
	 * Sets the default properties.
	 *
	 * @return array
	 */
	protected function defaults(): array
	{
		return [
			'labels'                => [],
			'description'           => '',
			'public'                => true,
			'publicly_queryable'    => null,
			'query_var'             => true,
			'exclude_from_search'   => null,
			'has_archive'           => true,
			'hierarchical'          => false,
			'rewrite'               => true,
			'supports'              => [],
			'taxonomies'            => [],
			'show_ui'               => null,
			'show_in_menu'          => null,
			'show_in_nav_menus'     => null,
			'show_in_admin_bar'     => null,
			'menu_position'         => null,
			'menu_icon'             => null,
			'register_meta_box_cb'  => null,
			'capability_type'       => 'post',
			'capabilities'          => [],
			'map_meta_cap'          => null,
			'can_export'            => true,
			'delete_with_user'      => null,
			'show_in_rest'          => false,
			'rest_base'             => null,
			'rest_controller_class' => null,
		];
	}

	/**
	 * Set defaults and register the post type.
	 *
	 * @throws \RuntimeException
	 */
	protected function register(): void
	{
		if (empty($this->properties['labels'])) {
			$this->labels();
		}

		if (empty($this->messages)) {
			$this->messages();
		}

		$object = register_post_type($this->name, $this->properties);

		if (!is_wp_error($object)) {
			$this->initialize($object);
		}

		$this->properties = [];
	}

	/**
	 * @param \WP_Post_Type $object
	 *
	 * @throws \RuntimeException
	 */
	protected function initialize(\WP_Post_Type $object): void
	{
		$this->object = $object;

		if ($this->filter && !$this->hierarchical) {
			$this->addRewriteRules();

			add_action('post_type_link', function ($link, \WP_Post $post) {
				if ($post->post_type === $this->name) {
					$link = \call_user_func($this->filter, $link, $post);
				}

				return $link;
			}, 1, 2);
		}

		if (is_admin()) {
			add_action('post_updated_messages', function ($messages) {
				return $this->addMessages($messages);
			});
		}
	}

	/**
	 * Enables permalinks like posts (width dates).
	 */
	protected function addRewriteRules(): void
	{
		$slug = $this->rewrite['slug'];

		$patterns = [
			['var' => 'year', 'regex' => '([0-9]{4})'],
			['var' => 'monthnum', 'regex' => '([0-9]{1,2})'],
			['var' => 'day', 'regex' => '([0-9]{1,2})'],
			['var' => $this->name, 'regex' => '([^/]+)'],
		];

		array_walk($patterns, function (&$value, $index) {
			$value['var'] = sprintf('%s=$matches[%d]', $value['var'], $index + 1);
		});

		for ($count = \count($patterns); $count > 0; $count--) {
			$slice = \array_slice($patterns, 0, $count);
			$regex = sprintf('^%s/%s/?', $slug, implode('/', Arr::pluck($slice, 'regex')));
			$query = sprintf('index.php?post_type=%s&%s', $this->name, implode('&', Arr::pluck($slice, 'var')));

			add_rewrite_rule($regex, $query, 'top');
		}
	}

	/**
	 * @param array $messages
	 *
	 * @return array
	 *
	 * @throws \RuntimeException
	 */
	protected function addMessages(array $messages): array
	{
		$post = get_post();

		if ($post->post_type === $this->name) {
			$messages[$this->name] = $this->getMessages($post);
		}

		return $messages;
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return array
	 *
	 * @throws \RuntimeException
	 */
	protected function getMessages(\WP_Post $post): array
	{
		$type = get_post_type_object($this->name);

		if (!($type instanceof \WP_Post_Type)) {
			throw new \RuntimeException(sprintf('Could not get post type of "%s"', $this->name));
		}

		$messages = $this->messages;
		$messages[5] = isset($_GET['revision']) ? sprintf($messages[5], wp_post_revision_title((int) $_GET['revision'], false)) : false;
		$messages[9] = sprintf($messages[9], date_i18n(__('M j, Y @ G:i', 'ic-framework'), strtotime($post->post_date)));

		if ($type->publicly_queryable) {
			$permalink = get_permalink($post->ID);
			$view      = ' ' . Tag::a(['href' => $permalink], sprintf(__('View %s', 'ic-framework'), strtolower($this->singular)));
			$preview   = ' ' . Tag::a([
				                          'href'   => add_query_arg('preview', 'true', $permalink),
				                          'target' => 'blank',
			                          ], sprintf(__('Preview %s', 'ic-framework'), strtolower($this->singular)));

			$messages[1] .= $view;
			$messages[6] .= $view;
			$messages[9] .= $view;

			$messages[8]  .= $preview;
			$messages[10] .= $preview;
		}

		return $messages;
	}

}