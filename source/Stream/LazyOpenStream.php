<?php

namespace ic\Framework\Stream;

/**
 * Class LazyOpenStream
 *
 * @package ic\Framework\Stream
 */
class LazyOpenStream implements StreamInterface
{

    use StreamDecorator;

    /**
     * @var string File to open
     */
    private $filename;

    /**
     * @var string $mode
     */
    private $mode;

    /**
     * @param string $filename File to lazily open
     * @param string $mode     fopen mode to use when opening the stream
     */
    public function __construct($filename, $mode)
    {
        $this->filename = $filename;
        $this->mode     = $mode;
    }

    /**
     * Creates the underlying stream lazily when required.
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return StreamInterface
     */
    protected function createStream()
    {
        return Stream::create(Utils::open($this->filename, $this->mode));
    }

}