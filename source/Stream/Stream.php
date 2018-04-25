<?php

namespace ic\Framework\Stream;

/**
 * Class Stream
 *
 * @package ic\Framework\Stream
 */
class Stream implements StreamInterface
{

    /**
     * @var resource
     */
    private $stream;

    /**
     * @var int
     */
    private $size;

    /**
     * @var bool
     */
    private $seekable;

    /**
     * @var bool
     */
    private $readable;

    /**
     * @var bool
     */
    private $writable;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var array
     */
    private $metadata;

    /**
     * @var array
     */
    private $filters = [];

    /**
     * Create a new stream based on the input type.
     *
     * This factory accepts the same associative array of options as described
     * in the constructor.
     *
     * @param resource|string|StreamInterface $resource Entity body data
     * @param array                           $options  Additional options
     *
     * @return StreamInterface
     * @throws \InvalidArgumentException if the $resource arg is not valid.
     */
    public static function create($resource = '', array $options = [])
    {
        $type = gettype($resource);

        if ($type === 'string') {
            $stream = fopen('php://temp', 'r+b');
            if ($resource !== '') {
                fwrite($stream, $resource);
                fseek($stream, 0);
            }

            return new static($stream, $options);
        }

        if ($type === 'resource') {
            return new static($resource, $options);
        }

        if ($resource instanceof StreamInterface) {
            return $resource;
        }

        if ($type === 'object' && method_exists($resource, '__toString')) {
            return static::create((string)$resource, $options);
        }

        if (is_callable($resource)) {
            return new PumpStream($resource, $options);
        }

        if ($resource instanceof \Iterator) {
            return new PumpStream(function () use ($resource) {
                if (!$resource->valid()) {
                    return false;
                }

                $result = $resource->current();
                $resource->next();

                return $result;
            }, $options);
        }

        throw new \InvalidArgumentException('Invalid resource type: ' . $type);
    }

    /**
     * This constructor accepts an associative array of options.
     *
     * - size: (int) If a read stream would otherwise have an indeterminate
     *   size, but the size is known due to foreknownledge, then you can
     *   provide that size, in bytes.
     * - metadata: (array) Any additional metadata to return when the metadata
     *   of the stream is accessed.
     *
     * @param resource $stream  Stream resource to wrap.
     * @param array    $options Associative array of options.
     *
     * @throws \InvalidArgumentException if the stream is not a stream resource
     */
    public function __construct($stream, array $options = [])
    {
        if (isset($options['size'])) {
            $this->size = (int)$options['size'];
        }

        $this->metadata = isset($options['metadata']) ? $options['metadata'] : [];

        $this->attach($stream);
    }

    /**
     * Closes the stream when the destructed
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        if (!$this->stream) {
            return '';
        }

        $this->seek(0);

        return (string)stream_get_contents($this->stream);
    }

    /**
     * @inheritdoc
     */
    public function getContents()
    {
        return $this->stream ? stream_get_contents($this->stream) : '';
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }

        $this->detach();

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws \InvalidArgumentException
     */
    public function attach($stream)
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('Stream must be a resource');
        }

        $this->stream   = $stream;
        $meta           = stream_get_meta_data($this->stream);
        $this->seekable = $meta['seekable'];
        $this->readable = Utils::isReadable($meta['mode']);
        $this->writable = Utils::isWritable($meta['mode']);
        $this->uri      = $this->getMetadata('uri');

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function detach()
    {
        $result = $this->stream;

        $this->stream   = $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getSize()
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (!$this->stream) {
            return null;
        }

        // Clear the stat cache if the stream has a URI
        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }

        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = (int)$stats['size'];

            return $this->size;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * @inheritdoc
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * @inheritdoc
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * @inheritdoc
     */
    public function eof()
    {
        return !$this->stream || feof($this->stream);
    }

    /**
     * @inheritdoc
     */
    public function tell()
    {
        return $this->stream ? ftell($this->stream) : false;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->seek(0);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return $this->seekable ? fseek($this->stream, $offset, $whence) === 0 : false;
    }

    /**
     * @inheritdoc
     */
    public function read($length)
    {
        return $this->readable ? fread($this->stream, $length) : false;
    }

    /**
     * @inheritdoc
     */
    public function write($string)
    {
        // We can't know the size after writing anything
        $this->size = null;

        return $this->writable ? fwrite($this->stream, $string) : false;
    }

    /**
     * @inheritdoc
     */
    public function getMetadata($key = null)
    {
        if ($this->stream === null) {
            return $key ? null : [];
        } elseif (!$key) {
            return $this->metadata + stream_get_meta_data($this->stream);
        } elseif (isset($this->metadata[$key])) {
            return $this->metadata[$key];
        }

        $meta = stream_get_meta_data($this->stream);

        return isset($meta[$key]) ? $meta[$key] : null;
    }

    /**
     * @param string $name
     * @param int    $mode
     *
     * @return bool
     */
    public function addFilter($name, $mode = STREAM_FILTER_READ)
    {
        if (!isset($this->filters[$name][$mode])) {
            $this->filters[$name][$mode] = stream_filter_append($this->stream, $name, $mode);

            return true;
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function removeFilter($name)
    {
        if (isset($this->filters[$name])) {
            array_map(function ($filter) {
                stream_filter_remove($filter);
            }, $this->filters[$name]);

            unset($this->filters[$name]);

            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        if (empty($this->filters)) {
            return $this->filters;
        }

        $names = array_keys($this->filters);
        $modes = array_map(function ($filter) {
            return array_keys($filter);
        }, $this->filters);

        return array_combine($names, $modes);
    }

}