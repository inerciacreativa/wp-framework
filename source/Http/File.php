<?php

namespace ic\Framework\Http;

/**
 * Class File
 *
 * @package ic\Framework\Http
 */
class File
{

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
     * @var string
     */
    private $size;

    /**
     * The UPLOAD_ERR_XXX constant provided by the uploader.
     *
     * @var int
     */
    private $error;

    public function __construct($path, $originalName, $mimeType = null, $size = null, $error = null)
    {
        $this->path         = $path;
        $this->originalName = $originalName;
        $this->mimeType     = $mimeType ?: 'application/octet-stream';
        $this->size         = $size;
        $this->error        = $error ?: UPLOAD_ERR_OK;
    }

    public function toArray()
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