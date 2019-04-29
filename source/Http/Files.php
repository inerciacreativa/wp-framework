<?php

namespace ic\Framework\Http;

use ic\Framework\Data\Repository;

/**
 * Class Files
 *
 * @package ic\Framework\Http
 */
class Files extends Repository
{

	/**
	 * InputStore constructor.
	 *
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		parent::__construct($this->normalizeFiles($items));
	}

	/**
	 * Normalize uploaded files.
	 *
	 * Transforms each value into an UploadedFile instance, and ensures
	 * that nested arrays are normalized.
	 *
	 * @param array $files
	 *
	 * @return array
	 */
	private function normalizeFiles(array $files): array
	{
		$normalized = [];

		foreach ($files as $key => $file) {
			if ($file instanceof File) {
				$normalized[$key] = $file;
				continue;
			}

			if (is_array($file) && isset($file['tmp_name'])) {
				$normalized[$key] = $this->createFile($file);
				continue;
			}

			if (is_array($file)) {
				$normalized[$key] = $this->normalizeFiles($file);
				continue;
			}
		}

		return $normalized;
	}

	/**
	 * Create and return an UploadedFile instance from a $_FILES specification.
	 *
	 * If the specification represents an array of values, this method will
	 * delegate to normalizeNestedFile() and return that return value.
	 *
	 * @param array $file $_FILES struct
	 *
	 * @return array|File
	 */
	private function createFile(array $file)
	{
		if (is_array($file['tmp_name'])) {
			return $this->normalizeNestedFile($file);
		}

		return new File($file['tmp_name'], $file['name'], $file['type'], (int) $file['size'], $file['error']);
	}

	/**
	 * Normalize an array of file specifications.
	 *
	 * Loops through all nested files and returns a normalized array of
	 * UploadedFile instances.
	 *
	 * @param array $files
	 *
	 * @return File[]
	 */
	private function normalizeNestedFile(array $files): array
	{
		$normalized = [];

		$keys = array_keys($files['tmp_name']);
		foreach ($keys as $key) {
			$file = [
				'tmp_name' => $files['tmp_name'][$key],
				'name'     => $files['name'][$key],
				'type'     => $files['type'][$key],
				'size'     => $files['size'][$key],
				'error'    => $files['error'][$key],
			];

			$normalized[$key] = $this->createFile($file);
		}

		return $normalized;
	}

}