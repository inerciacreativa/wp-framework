<?php
namespace ic\Framework\Stream;

/**
 * Class StreamDecorator
 *
 * @package ic\Framework\Stream
 * @property StreamInterface stream
 */
trait StreamDecorator
{

    /**
     * @param StreamInterface $stream Stream to decorate
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Magic method used to create a new stream if streams are not added in
     * the constructor of a decorator (e.g., LazyOpenStream).
     *
     * @param string $name Name of the property (allows "stream" only).
     *
     * @throws \UnexpectedValueException
     *
     * @return StreamInterface
     */
    public function __get($name)
    {
        if ($name === 'stream') {
            $this->stream = $this->createStream();

            return $this->stream;
        }

        throw new \UnexpectedValueException("$name not found on class");
    }

    /**
     * Allow decorators to implement custom methods
     *
     * @param string $method Missing method name
     * @param array  $args   Method arguments
     *
     * @return mixed
     */
    public function __call($method, array $args)
    {
        $result = call_user_func_array([$this->stream, $method], $args);

        // Always return the wrapped object if the result is a return $this
        return $result === $this->stream ? $this : $result;
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }

            return $this->getContents();
        } catch (\Exception $exception) {
            // Really, PHP? https://bugs.php.net/bug.php?id=53648
            trigger_error('StreamDecorator::__toString exception: ' . (string)$exception, E_USER_ERROR);

            return '';
        }
    }

    /**
     * @inheritdoc
     *
     * @throws \RuntimeException
     */
    public function getContents()
    {
        return Utils::copyToString($this);
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        $this->stream->close();
    }

    /**
     * @inheritdoc
     */
    public function getMetadata($key = null)
    {
        return $this->stream->getMetadata($key);
    }

    /**
     * @inheritdoc
     *
     * @throws \RuntimeException
     */
    public function attach($stream)
    {
        throw new \RuntimeException('Cannot attach Stream to StreamDecorator');
    }

    /**
     * @inheritdoc
     */
    public function detach()
    {
        return $this->stream->detach();
    }

    /**
     * @inheritdoc
     */
    public function getSize()
    {
        return $this->stream->getSize();
    }

    /**
     * @inheritdoc
     */
    public function eof()
    {
        return $this->stream->eof();
    }

    /**
     * @inheritdoc
     *
     * @throws \RuntimeException
     */
    public function tell()
    {
        return $this->stream->tell();
    }

    /**
     * @inheritdoc
     */
    public function isReadable()
    {
        return $this->stream->isReadable();
    }

    /**
     * @inheritdoc
     */
    public function isWritable()
    {
        return $this->stream->isWritable();
    }

    /**
     * @inheritdoc
     */
    public function isSeekable()
    {
        return $this->stream->isSeekable();
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
        $this->stream->seek($offset, $whence);
    }

    /**
     * @inheritdoc
     *
     * @throws \RuntimeException
     */
    public function read($length)
    {
        return $this->stream->read($length);
    }

    /**
     * @inheritdoc
     *
     * @throws \RuntimeException
     */
    public function write($string)
    {
        return $this->stream->write($string);
    }

    /**
     * Implement in subclasses to dynamically create streams when requested.
     *
     * @return StreamInterface
     * @throws \BadMethodCallException
     */
    protected function createStream()
    {
        throw new \BadMethodCallException('createStream() not implemented in ' . get_class($this));
    }

}