<?php

namespace ic\Framework\Stream;

/**
 * Class AppendStream
 *
 * @package ic\Framework\Stream
 */
class AppendStream implements StreamInterface
{

    /**
     * @var StreamInterface[] $streams
     */
    private $streams = [];

    /**
     * @var bool
     */
    private $seekable = true;

    /**
     * @var int
     */
    private $current = 0;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var bool
     */
    private $detached = false;

    /**
     * @param StreamInterface[] $streams Streams to decorate. Each stream must be readable.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $streams = [])
    {
        foreach ($streams as $stream) {
            $this->addStream($stream);
        }
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        try {
            $this->rewind();

            return $this->getContents();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Add a stream to the AppendStream
     *
     * @param StreamInterface $stream Stream to append. Must be readable.
     *
     * @throws \InvalidArgumentException if the stream is not readable
     */
    public function addStream(StreamInterface $stream)
    {
        if (!$stream->isReadable()) {
            throw new \InvalidArgumentException('Each stream must be readable');
        }

        // The stream is only seekable if all streams are seekable
        if (!$stream->isSeekable()) {
            $this->seekable = false;
        }

        $this->streams[] = $stream;
    }

    /**
     * @inheritdoc
     */
    public function getContents()
    {
        return Utils::copyToString($this);
    }

    /**
     * Closes each attached stream.
     *
     * {@inheritdoc}
     */
    public function close()
    {
        $this->position = $this->current = 0;

        foreach ($this->streams as $stream) {
            $stream->close();
        }

        $this->streams = [];
    }

    /**
     * @inheritdoc
     *
     * @throws \RuntimeException
     */
    public function attach($stream)
    {
        throw new \RuntimeException('Cannot attach Stream to AppendStream');
    }

    /**
     * Detaches each attached stream
     *
     * {@inheritdoc}
     */
    public function detach()
    {
        $this->close();
        $this->detached = true;
    }

    /**
     * @inheritdoc
     */
    public function tell()
    {
        return $this->position;
    }

    /**
     * Tries to calculate the size by adding the size of each stream.
     *
     * If any of the streams do not return a valid number, then the size of the
     * append stream cannot be determined and null is returned.
     *
     * {@inheritdoc}
     */
    public function getSize()
    {
        $size = 0;
        foreach ($this->streams as $stream) {
            $streamSize = $stream->getSize();

            if ($streamSize === null) {
                return null;
            }

            $size += $streamSize;
        }

        return $size;
    }

    /**
     * @inheritdoc
     */
    public function eof()
    {
        return !$this->streams || ($this->current >= count($this->streams) - 1 && $this->streams[$this->current]->eof());
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * Attempts to seek to the given position. Only supports SEEK_SET.
     *
     * @throws \RuntimeException
     *
     * @inheritdoc
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->seekable || $whence !== SEEK_SET) {
            return false;
        }

        $success = true;

        $this->position = $this->current = 0;

        // Rewind each stream
        foreach ($this->streams as $stream) {
            if (!$stream->rewind()) {
                $success = false;
            }
        }

        if (!$success) {
            return false;
        }

        // Seek to the actual position by reading from each stream
        while ($this->position < $offset && !$this->eof()) {
            $this->read(min(8096, $offset - $this->position));
        }

        return $this->position === $offset;
    }

    /**
     * Reads from all of the appended streams until the length is met or EOF.
     *
     * {@inheritdoc}
     */
    public function read($length)
    {
        $buffer    = '';
        $total     = count($this->streams) - 1;
        $remaining = $length;

        while ($remaining > 0) {
            // Progress to the next stream if needed.
            if ($this->streams[$this->current]->eof()) {
                if ($this->current === $total) {
                    break;
                }

                $this->current++;
            }

            $buffer .= $this->streams[$this->current]->read($remaining);
            $remaining = $length - strlen($buffer);
        }

        $this->position += strlen($buffer);

        return $buffer;
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
        return false;
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
    public function write($string)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getMetadata($key = null)
    {
        return $key ? null : [];
    }

}