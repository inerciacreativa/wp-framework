<?php

namespace ic\Framework\Stream;

/**
 * Class StreamWrapper
 *
 * @package ic\Framework\Stream
 */
class StreamWrapper
{

    const PROTOCOL = 'ic';

    const RESOURCE = 'ic://stream';

    /**
     * @var resource
     */
    public $context;

    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * @var string r, r+, or w
     */
    private $mode;

    /**
     * Returns a resource representing the stream.
     *
     * @param StreamInterface $stream The stream to get a resource for
     *
     * @throws \InvalidArgumentException if stream is not readable or writable
     *
     * @return resource
     */
    public static function getResource(StreamInterface $stream)
    {
        static::register();

        if ($stream->isReadable()) {
            $mode = $stream->isWritable() ? 'r+b' : 'rb';
        } elseif ($stream->isWritable()) {
            $mode = 'wb';
        } else {
            throw new \InvalidArgumentException('The stream must be readable, writable, or both.');
        }

        return fopen(static::RESOURCE, $mode, null, stream_context_create([
            static::PROTOCOL => ['stream' => $stream],
        ]));
    }

    /**
     * Registers the stream wrapper if needed
     */
    public static function register()
    {
        if (!in_array(static::PROTOCOL, stream_get_wrappers(), false)) {
            stream_wrapper_register(static::PROTOCOL, __CLASS__);
        }
    }

    public function stream_cast()
    {
        return false;
    }

    /**
     * Opens stream.
     *
     * @param string $path
     * @param string $mode
     * @param int    $unused
     * @param string $opened_path
     *
     * @return bool
     */
    public function stream_open($path, $mode, $unused, &$opened_path)
    {
        $options = stream_context_get_options($this->context);

        if (!isset($options[static::PROTOCOL]['stream'])) {
            return false;
        }

        $this->mode   = $mode;
        $this->stream = $options[static::PROTOCOL]['stream'];

        return true;
    }

    /**
     * Read from stream.
     *
     * @param int $count
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function stream_read($count)
    {
        return $this->stream->read($count);
    }

    /**
     * Write to stream.
     *
     * @param string $data
     *
     * @throws \RuntimeException
     *
     * @return int
     */
    public function stream_write($data)
    {
        return (int)$this->stream->write($data);
    }

    /**
     * Retrieve the current position of a stream.
     *
     * @throws \RuntimeException
     *
     * @return int
     */
    public function stream_tell()
    {
        return $this->stream->tell();
    }

    /**
     * Tests for end-of-file on a stream pointer.
     *
     * @return bool
     */
    public function stream_eof()
    {
        return $this->stream->eof();
    }

    /**
     * Seeks to specific location in a stream.
     *
     * @param int $offset
     * @param int $whence
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    public function stream_seek($offset, $whence = SEEK_SET)
    {
        return $this->stream->seek($offset, $whence);
    }

    /**
     * Retrieve information about a stream resource.
     *
     * @return array
     */
    public function stream_stat()
    {
        static $modeMap = [
            'r'  => 33060,
            'r+' => 33206,
            'w'  => 33188,
        ];

        return [
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => $modeMap[$this->mode],
            'nlink'   => 0,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => $this->stream->getSize() ?: 0,
            'atime'   => 0,
            'mtime'   => 0,
            'ctime'   => 0,
            'blksize' => 0,
            'blocks'  => 0,
        ];
    }

}