<?php

namespace ic\Framework\Type\MetaBox;

use ic\Framework\Framework;
use ic\Framework\Html\Tag;
use ic\Framework\Support\Arr;
use ic\Framework\Type\Taxonomy;

class TaxonomyMetaBox
{

	/**
	 * @var Taxonomy
	 */
	protected $taxonomy;

	/**
	 * @var bool
	 */
	protected $showPopular = true;

	/**
	 * @var bool
	 */
	protected $allowMultiple = true;

	/**
	 * @var bool
	 */
	protected $allowNone = true;

	/**
	 * TaxonomyMetaBox constructor.
	 *
	 * @param Taxonomy $taxonomy
	 * @param bool     $popular
	 * @param bool     $multiple
	 * @param bool     $allowNone
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct(Taxonomy $taxonomy, bool $popular = true, bool $multiple = true, bool $allowNone = true)
	{
		$this->taxonomy      = $taxonomy;
		$this->showPopular   = $popular;
		$this->allowMultiple = $multiple;
		$this->allowNone     = $allowNone;

		Framework::instance()->addBackScript('taxonomy-meta-box.js', [
			'depends' => ['wp-lists'],
			'hooks'   => ['post.php', 'post-new.php'],
		]);

		add_action('registered_taxonomy', function ($taxonomy) {
			if ($this->taxonomy->name !== $taxonomy) {
				return;
			}

			remove_action('wp_ajax_add-' . $taxonomy, '_wp_ajax_add_hierarchical_term');
			add_action('wp_ajax_add-' . $taxonomy, function () {
				$this->addNewTerm();
			});
		});
	}

	/**
	 * @param \WP_Post $post
	 * @param array    $box
	 */
	public function __invoke(\WP_Post $post, array $box = [])
	{
		$this->render($post);
	}

	/**
	 * Displays terms controls.
	 *
	 * @param \WP_Post $post
	 *
	 * @see post_categories_meta_box()
	 */
	protected function render(\WP_Post $post): void
	{
		$taxonomy = $this->taxonomy->name;
		$selected = $this->getPostTerms($post);
		$popular  = $this->getPopularTerms();
		$class    = 'categorydiv ic-meta-box';

		if ($this->allowMultiple()) {
			$class .= ' ic-meta-box-multiple';
		}

		if ($this->isHierarchical()) {
			$class .= ' ic-meta-box-hierarchical';
		}

		echo Tag::div(['id' => "taxonomy-$taxonomy", 'class' => $class], [
			$this->getTabs(),
			$this->getPopularTab($selected, $popular),
			$this->getAllTab($post, $selected, $popular),
			$this->getNewTerm(),
		]);
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	protected function getPostTerms(\WP_Post $post): array
	{
		$terms = $post ? $this->taxonomy->getPostTerms($post->ID, ['fields' => 'ids']) : [];

		if (\count($terms) > 1 && !$this->allowMultiple()) {
			$terms = \array_slice($terms, 0, 1);
		}

		if (empty($terms) && $this->allowNone()) {
			$terms[] = 0;
		}

		return $terms;
	}

	/**
	 * @return array
	 */
	protected function getPopularTerms(): array
	{
		return $this->showPopular() ? $this->taxonomy->getPopularTerms() : [];
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	protected function getLabel(string $key): string
	{
		return $this->taxonomy->labels->$key;
	}

	/**
	 * Renders the tabs to switch between all and popular terms.
	 *
	 * @return Tag
	 */
	protected function getTabs(): Tag
	{
		$taxonomy = $this->taxonomy->name;

		$tabs = Tag::ul([
			'id'    => "$taxonomy-tabs",
			'class' => 'category-tabs',
		], [
			Tag::li(['class' => 'tabs'], Tag::a(['href' => "#$taxonomy-all"], $this->getLabel('all_items'))),
		]);

		if ($this->showPopular()) {
			$tabs->content(Tag::li(['class' => 'hide-if-no-js'], Tag::a(['href' => "#$taxonomy-pop"], __('Most Used'))));
		}

		return $tabs;
	}

	/**
	 * Renders the popular (most used) terms.
	 *
	 * @param array $selected
	 * @param array $popular
	 *
	 * @return Tag|null
	 */
	protected function getPopularTab(array $selected, array $popular): ?Tag
	{
		if (!$this->showPopular()) {
			return null;
		}

		$taxonomy = $this->taxonomy->name;
		$disabled = !$this->userCan('assign_terms');

		return Tag::div([
			'id'    => "$taxonomy-pop",
			'class' => 'tabs-panel',
			'style' => 'display: none;',
		], Tag::ul([
			'id'    => "${taxonomy}checklist-pop",
			'class' => 'categorychecklist form-no-clear',
		], array_map(function ($term) use ($selected, $disabled) {
			return $this->getInput($term, [
				'prefix'   => 'popular-',
				'class'    => 'popular-category',
				'name'     => '',
				'checked'  => \in_array($term->term_id, $selected, false),
				'disabled' => $disabled,
			]);
		}, $popular)));
	}

	/**
	 * Renders the controls to select the terms.
	 *
	 * @param \WP_Post $post
	 * @param array    $selected
	 * @param array    $popular Popular terms.
	 *
	 * @uses wp_terms_checklist()
	 *
	 * @return Tag
	 */
	protected function getAllTab(\WP_Post $post, array $selected, array $popular): Tag
	{
		$taxonomy = $this->taxonomy->name;
		$popular  = empty($popular) ? [] : Arr::pluck($popular, 'term_id');

		$all = Tag::div([
			'id'    => "$taxonomy-all",
			'class' => 'tabs-panel',
		], Tag::ul([
			'id'            => "${taxonomy}checklist",
			'data-wp-lists' => "list:$taxonomy",
			'class'         => 'categorychecklist form-no-clear',
		], wp_terms_checklist($post->ID, [
			'taxonomy'      => $taxonomy,
			'selected_cats' => $selected,
			'popular_cats'  => $popular,
			'checked_ontop' => true,
			'walker'        => new TaxonomyWalker($this),
			'echo'          => false,
		])));

		if ($this->isHierarchical()) {
			$all->content(Tag::input([
				'type'  => 'hidden',
				'name'  => "tax_input[$taxonomy][]",
				'value' => 0,
			]));
		}

		return $all;
	}

	/**
	 * Renders the controls to add a new term.
	 *
	 * @return Tag|null
	 */
	protected function getNewTerm(): ?Tag
	{
		if (!$this->userCan('edit_terms')) {
			return null;
		}

		$taxonomy = $this->taxonomy->name;

		return Tag::div([
			'id'    => "$taxonomy-adder",
			'class' => 'wp-hidden-children',
		], [
			// Trigger to show the controls.
			Tag::a([
				'id'    => "$taxonomy-add-toggle",
				'href'  => "#$taxonomy-add",
				'class' => 'hide-if-no-js taxonomy-add-new',
			], sprintf(__('+ %s'), $this->getLabel('add_new_item'))),

			// Controls to add the term.
			Tag::p([
				'id'    => "$taxonomy-add",
				'class' => 'category-add wp-hidden-child',
			], [
				// Text input for the term.
				Tag::label([
					'for'   => "new$taxonomy",
					'class' => 'screen-reader-text',
				], $this->getLabel('add_new_item')),

				Tag::input([
					'type'          => 'text',
					'id'            => "new$taxonomy",
					'name'          => "new$taxonomy",
					'value'         => $this->getLabel('new_item_name'),
					'class'         => 'form-required form-input-tip',
					'aria-required' => 'true',
				]),

				// Parent term selector.
				$this->getNewParent(),

				// Button.
				Tag::input([
					'type'          => 'button',
					'id'            => "$taxonomy-add-submit",
					'value'         => $this->getLabel('add_new_item'),
					'data-wp-lists' => "add:${taxonomy}checklist:${taxonomy}-add",
					'class'         => 'button category-add-submit',
				]),

				wp_nonce_field("add-$taxonomy", "_ajax_nonce-add-$taxonomy", false, false),

				Tag::span(['id' => "$taxonomy-ajax-response"]),
			]),
		]);
	}

	/**
	 * Renders a dropdown with the terms to be assigned as parent for hierarchical taxonomies.
	 *
	 * @uses wp_dropdown_categories()
	 *
	 * @return array|null
	 */
	protected function getNewParent(): ?array
	{
		if (!$this->isHierarchical() || !$this->allowMultiple()) {
			return null;
		}

		$taxonomy  = $this->taxonomy->name;
		$arguments = apply_filters('post_edit_category_parent_dropdown_args', [
			'taxonomy'         => $taxonomy,
			'name'             => "new${taxonomy}_parent",
			'orderby'          => 'name',
			'hide_empty'       => false,
			'hierarchical'     => true,
			'show_option_none' => '&mdash; ' . $this->getLabel('parent_item') . ' &mdash;',
		]);

		$arguments['echo'] = false;

		return [
			Tag::label([
				'for'   => "new${taxonomy}_parent",
				'class' => 'screen-reader-text',
			], $this->getLabel('parent_item_colon')),
			wp_dropdown_categories($arguments),
		];
	}

	/**
	 * Ajax handler for adding a term.
	 *
	 * @uses \WP_Ajax_Response
	 * @see  _wp_ajax_add_hierarchical_term()
	 */
	protected function addNewTerm(): void
	{
		$action   = $_POST['action'];
		$taxonomy = $this->taxonomy->name;
		$response = null;

		check_ajax_referer($action, '_ajax_nonce-add-' . $taxonomy);
		if (!$this->userCan('edit_terms')) {
			wp_die(-1);
		}

		$names = explode(',', $_POST['new' . $taxonomy]);

		foreach ($names as $name) {
			$name = trim($name);
			if (sanitize_title($name) === '') {
				continue;
			}

			if (!$term_id = term_exists($name, $taxonomy)) {
				$term_id = wp_insert_term($name, $taxonomy);
			}

			if (is_wp_error($term_id)) {
				continue;
			}

			$term = (object) [
				'term_id' => \is_array($term_id) ? $term_id['term_id'] : $term_id,
				'name'    => $name,
			];

			$data = $this->getInput($term, [
				'checked' => true,
				'space'   => '&nbsp;',
			]);

			$response = [
				'what'     => $taxonomy,
				'id'       => $term_id,
				'data'     => str_replace(["\n", "\t"], '', $data),
				'position' => -1,
			];
		}

		if ($response) {
			$ajax = new \WP_Ajax_Response($response);
			$ajax->send();
		}
	}

	/**
	 * Creates an empty term and add it to the end of the list.
	 *
	 * @param array $terms
	 *
	 * @return array
	 */
	public function addNoneTerm(array $terms): array
	{
		if ($this->allowNone()) {
			$none = (object) [
				'term_id' => 0,
				'name'    => '&mdash; ' . __('None') . ' &mdash;',
				'slug'    => '',
				'parent'  => 0,
			];

			$terms[] = $none;
		}

		return $terms;
	}

	/**
	 * Creates a input control (radio or checkbox) for the term.
	 *
	 * @param object $term
	 * @param array  $attributes
	 *
	 * @see /public/js/taxonomy-meta-box.js
	 *
	 * @return Tag
	 */
	public function getInput($term, array $attributes = []): Tag
	{
		$taxonomy = $this->taxonomy->name;

		$prefix = Arr::pull($attributes, 'prefix', '');
		$class  = Arr::pull($attributes, 'class', '');
		$space  = Arr::pull($attributes, 'space', '');

		$id   = "$prefix$taxonomy-$term->term_id";
		$name = "tax_input[$taxonomy]" . ($this->isHierarchical() ? '[]' : '');
		$type = $this->allowMultiple() ? 'checkbox' : 'radio';

		$attributes = array_merge([
			'type'       => $type,
			'id'         => "in-$id",
			'name'       => $name,
			'value'      => $term->term_id,
			'data-value' => $term->name,
			// Used for non hierarchical taxonomies.
		], $attributes);

		return Tag::li([
			'id'    => $id,
			'class' => $class,
		], Tag::label(['class' => 'selectit'], [
			Tag::input($attributes),
			$space,
			esc_html(apply_filters('the_category', $term->name)),
		]));
	}

	/**
	 * Whether the taxonomy is hierarchical.
	 *
	 * @return bool
	 */
	public function isHierarchical(): bool
	{
		return $this->taxonomy->hierarchical;
	}

	/**
	 * Whether to show the most used terms.
	 *
	 * @return bool
	 */
	public function showPopular(): bool
	{
		return $this->showPopular;
	}

	/**
	 * Whether to allow select multiple terms in the metabox.
	 *
	 * @return bool
	 */
	public function allowMultiple(): bool
	{
		return $this->allowMultiple;
	}

	/**
	 * Whether to allow to not select terms.
	 *
	 * @return bool
	 */
	public function allowNone(): bool
	{
		return !$this->allowMultiple && $this->allowNone;
	}

	/**
	 * Whether the current user has a specific capability.
	 *
	 * @param string $capability
	 *
	 * @return bool
	 */
	protected function userCan(string $capability): bool
	{
		return current_user_can($this->taxonomy->cap->$capability);
	}

}