<?php

namespace ic\Framework\Stream;

/**
 * Class InflateStream
 *
 * @package ic\Framework\Stream
 */
class InflateStream implements StreamInterface
{

    use StreamDecorator;

    /**
     * InflateStream constructor.
     *
     * @param StreamInterface $stream
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct(StreamInterface $stream)
    {
        if ($stream->isSeekable()) {
            $stream->seek(0);
        }

        $compressed = $stream->getContents();

        if (false === ($decompressed = Utils::inflate($compressed))) {
            throw new \RuntimeException('Cannot inflate the stream contents');
        }

        $this->stream = Stream::create($decompressed);
    }

}