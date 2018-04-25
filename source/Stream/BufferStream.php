<?php

namespace ic\Framework\Stream;

/**
 * Class BufferStream
 *
 * @package ic\Framework\Stream
 */
class BufferStream implements StreamInterface
{

    /**
     * @var int
     */
    private $hwm;

    /**
     * @var string
     */
    private $buffer = '';

    /**
     * @param int $hwm High water mark, representing the preferred maximum
     *                 buffer size. If the size of the buffer exceeds the high
     *                 water mark, then calls to write will continue to succeed
     *                 but will return false to inform writers to slow down
     *                 until the buffer has been drained by reading from it.
     */
    public function __construct($hwm = 16384)
    {
        $this->hwm = $hwm;
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        try {
            $content = $this->getContents();
        } catch (\RuntimeException $exception) {
            $content = '';
        }

        return $content;
    }

    /**
     * @inheritdoc
     */
    public function getContents()
    {
        $buffer       = $this->buffer;
        $this->buffer = '';

        return $buffer;
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        $this->buffer = '';
    }

    /**
     * @inheritdoc
     *
     * @throws \RuntimeException
     */
    public function attach($stream)
    {
        throw new \RuntimeException('Cannot attach Stream to BufferStream');
    }

    /**
     * @inheritdoc
     */
    public function detach()
    {
        $this->close();
    }

    /**
     * @inheritdoc
     */
    public function getSize()
    {
        return strlen($this->buffer);
    }

    /**
     * @inheritdoc
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isWritable()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isSeekable()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * @inheritdoc
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function eof()
    {
        return $this->getSize() === 0;
    }

    /**
     * @inheritdoc
     */
    public function tell()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function read($length)
    {
        if ($length >= $this->getSize()) {
            // No need to slice the buffer because we don't have enough data.
            $result       = $this->buffer;
            $this->buffer = '';
        } else {
            // Slice up the result to provide a subset of the buffer.
            $result       = substr($this->buffer, 0, $length);
            $this->buffer = substr($this->buffer, $length);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function write($string)
    {
        $this->buffer .= $string;

        if ($this->getSize() >= $this->hwm) {
            return false;
        }

        return $this->getSize();
    }

    /**
     * @inheritdoc
     */
    public function getMetadata($key = null)
    {
        if ($key === 'hwm') {
            return $this->hwm;
        }

        return $key ? null : [];
    }

}