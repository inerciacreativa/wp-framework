<?php

namespace ic\Framework\Http;

/**
 * Class File
 *
 * @package ic\Framework\Http
 */
class File
{

	/**
	 * @var string
	 */
	private $path;

	/**
	 * The original name of the uploaded file.
	 *
	 * @var string
	 */
	private $originalName;

	/**
	 * The mime type provided by the uploader.
	 *
	 * @var string
	 */
	private $mimeType;

	/**
	 * The file size provided by the uploader.
	 *
	 * @var int
	 */
	private $size;

	/**
	 * The UPLOAD_ERR_XXX constant provided by the uploader.
	 *
	 * @var int
	 */
	private $error;

	/**
	 * File constructor.
	 *
	 * @param string      $path
	 * @param string      $originalName
	 * @param string|null $mimeType
	 * @param int         $size
	 * @param int|null    $error
	 */
	public function __construct(string $path, string $originalName, string $mimeType = null, $size = 0, int $error = null)
	{
		$this->path         = $path;
		$this->originalName = $originalName;
		$this->mimeType     = $mimeType ?: 'application/octet-stream';
		$this->size         = (int) $size;
		$this->error        = $error ?: UPLOAD_ERR_OK;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'tmp_name' => $this->path,
			'name'     => $this->originalName,
			'type'     => $this->mimeType,
			'size'     => $this->size,
			'error'    => $this->error,
		];
	}

}