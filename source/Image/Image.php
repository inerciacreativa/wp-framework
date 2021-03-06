<?php

namespace ic\Framework\Image;

use ic\Framework\Support\Arr;
use ic\Framework\Support\Url;
use WP_Error;
use WP_Image_Editor;

/**
 * Class Image
 *
 * @package ic\Framework\Image
 */
class Image
{

	public const JPEG_QUALITY = 90;

	public const IMAGE_TYPES = 'jpe?g|gif|png';

	private $id = 0;

	private $source = '';

	private $alt = '';

	private $post = 0;

	private $errors = [];

	private $local = false;

	/**
	 * Image constructor.
	 *
	 * @param $image
	 */
	public function __construct($image)
	{
		if (is_string($image)) {
			$this->setSource($image);
		} else if (is_array($image)) {
			$image  = array_merge([
				'id'  => 0,
				'src' => '',
				'alt' => '',
			], $image);
			$result = false;

			if ($image['id']) {
				$result = $this->setId($image['id']);
			}

			if (!$result && $image['src']) {
				$result = $this->setSource($image['src']);
			}

			if ($result) {
				$this->alt = Arr::get($image, 'alt', '');
			}
		} else if (is_numeric($image)) {
			$this->setId(absint($image));
		}
	}

	/**
	 * @param $image
	 *
	 * @return static
	 */
	public static function make($image)
	{
		return new static($image);
	}

	/**
	 * @param $code
	 * @param $message
	 *
	 * @return bool
	 */
	protected function addError($code, $message): bool
	{
		$this->errors[] = new WP_Error($code, $message);

		return false;
	}

	/**
	 * @return bool
	 */
	public function hasErrors(): bool
	{
		return !empty($this->errors);
	}

	/**
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	protected function setId(int $id): bool
	{
		if ($id) {
			$post = get_post($id);

			if (is_object($post) && ($post->post_type === 'attachment')) {
				$this->id    = $id;
				$this->post  = (int) $post->post_parent;
				$this->local = true;

				return true;
			}
		}

		return $this->addError('set_id', sprintf(__('Not valid image ID: %s', 'ic-framework'), $id));
	}

	/**
	 * @return string
	 */
	public function getSource(): string
	{
		return $this->source;
	}

	/**
	 * @param string $source
	 *
	 * @return bool
	 */
	protected function setSource(string $source): bool
	{
		$parsed = Url::parse($source);
		$home   = Url::parse(home_url());

		if (!$parsed->isValid()) {
			return $this->addError('set_url', sprintf(__('Not valid URL: %s', 'ic-framework'), $source));
		}

		if (empty($parsed->scheme)) {
			$parsed->scheme = $home->scheme;
		}

		if (empty($parsed->host)) {
			$parsed->host = $home->host;
		}

		$source = $parsed->render();

		if ($parsed->host === $home->host) {
			$base = wp_upload_dir();
			$base = $base['baseurl'] . '/';

			if (false === strpos($source, $base)) {
				return $this->addError('set_url', sprintf(__('Not valid local URL: %s', 'ic-framework'), $source));
			}

			return $this->setLocalUrl($source, $base);
		}

		$this->source = $source;

		return true;
	}

	/**
	 * @param string $url
	 * @param string $base
	 *
	 * @return bool
	 */
	protected function setLocalUrl(string $url, string $base): bool
	{
		global $wpdb;

		$slug = str_replace($base, '', $url);
		$slug = preg_replace('/-\d+x\d+(?=\.(' . self::IMAGE_TYPES . ')$)/i', '', $slug);

		/** @noinspection SqlResolve */
		$result = $wpdb->get_row($wpdb->prepare("SELECT posts.ID, posts.post_parent FROM $wpdb->posts AS posts, $wpdb->postmeta AS meta WHERE posts.ID = meta.post_id AND meta.meta_key = '_wp_attached_file' AND meta.meta_value = '%s' AND posts.post_type = 'attachment' LIMIT 1", $slug));

		if ($result) {
			$this->source = $url;
			$this->id     = (int) $result->id;
			$this->post   = (int) $result->post_parent;
			$this->local  = true;

			return true;
		}

		return $this->addError('local_url', sprintf(__('Not valid slug: %s (%s)', 'ic-framework'), $slug, $url));
	}

	/**
	 * @return bool
	 */
	public function isLocal(): bool
	{
		return $this->local;
	}

	/**
	 * @return bool
	 */
	public function isRemote(): bool
	{
		return !$this->local;
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool|int
	 */
	public function download(int $post_id = 0)
	{
		if ($this->isLocal()) {
			return $this->id;
		}

		if (!$this->isRemote()) {
			return $this->addError('download', __('Not a remote image', 'ic-framework'));
		}

		self::load();

		if (!preg_match('/[^\?]+\.(' . self::IMAGE_TYPES . ')\b/i', $this->source, $matches)) {
			return $this->addError('download', sprintf(__('Could not find the image in %s'), $this->source));
		}

		$temp = [
			'name'     => basename($matches[0]),
			'tmp_name' => download_url($this->source),
		];

		if (is_wp_error($temp['tmp_name'])) {
			$error = $temp['tmp_name'];

			return $this->addError('download', $error->get_error_message());
		}

		$id = media_handle_sideload($temp, $post_id, $this->alt);

		if (is_wp_error($id)) {
			@unlink($temp['tmp_name']);

			return $this->addError('download', $id->get_error_message());
		}

		$this->id    = $id;
		$this->post  = $post_id;
		$this->local = true;

		return $this->id;
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool
	 */
	public function setFeatured(int $post_id = 0): bool
	{
		if ($post_id && $this->isRemote()) {
			$this->download($post_id);
		}

		if ($this->isLocal()) {
			set_post_thumbnail($this->post, $this->id);

			return true;
		}

		return $this->addError('set_featured', __('Not a local image', 'ic-framework'));
	}

	/**
	 * @param int  $width
	 * @param int  $height
	 * @param bool $crop
	 *
	 * @return array|bool
	 */
	public function get(int $width, int $height, bool $crop = false)
	{
		if (!$this->isLocal()) {
			return $this->addError('get', __('Not a local image', 'ic-framework'));
		}

		$full = wp_get_attachment_image_src($this->id, 'full');
		$file = get_attached_file($this->id);
		$info = pathinfo($file);
		$base = $info['dirname'] . '/' . $info['filename'];
		$type = '.' . $info['extension'];

		if ($full[1] > $width || $full[2] > $height) {
			if (!$crop) {
				$resize = wp_constrain_dimensions($full[1], $full[2], $width, $height);

				[$width, $height] = $resize;
			}

			$image = $base . '-' . $width . 'x' . $height . $type;

			if (!file_exists($image)) {
				$image = static::resize($file, $width, $height, $crop);

				if (is_wp_error($image)) {
					return $this->addError('resize', $image->get_error_message());
				}

				$resize = getimagesize($image);

				[$width, $height] = $resize;
			}

			$url = str_replace(basename($full[0]), basename($image), $full[0]);
		} else {
			[$url, $width, $height] = $full;
		}

		return [
			'url'    => $url,
			'width'  => $width,
			'height' => $height,
		];
	}

	/**
	 * @param string $file
	 * @param int    $width
	 * @param int    $height
	 * @param bool   $crop
	 *
	 * @return string|WP_Error
	 */
	protected static function resize(string $file, int $width, int $height, bool $crop)
	{
		/** @var WP_Image_Editor $editor */
		$editor = wp_get_image_editor($file);
		if (is_wp_error($editor)) {
			return $editor;
		}

		$editor->set_quality(self::JPEG_QUALITY);

		$resize = $editor->resize($width, $height, $crop);
		if (is_wp_error($resize)) {
			return $resize;
		}

		$generated = $editor->generate_filename();
		$saved     = $editor->save($generated);

		if (is_wp_error($saved)) {
			return $saved;
		}

		return $generated;
	}

	/**
	 *
	 */
	protected static function load(): void
	{
		static $loaded = false;

		if ($loaded) {
			return;
		}

		if (!function_exists('download_url')) {
			include ABSPATH . 'wp-admin/includes/file.php';
		}

		if (!function_exists('media_handle_sideload')) {
			include ABSPATH . 'wp-admin/includes/media.php';
		}

		if (!function_exists('wp_read_image_metadata')) {
			include ABSPATH . 'wp-admin/includes/image.php';
		}

		$loaded = true;
	}

}
