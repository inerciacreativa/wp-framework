<?php

namespace ic\Framework\Stream;

/**
 * Class LazyInflateStream
 *
 * @package ic\Framework\Stream
 */
class LazyInflateStream implements StreamInterface
{

    use StreamDecorator;

    /**
     * LazyInflateStream constructor.
     *
     * @param StreamInterface $stream
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct(StreamInterface $stream)
    {
        $stream   = new LimitStream($stream, -1, $this->getOffset($stream));
        $resource = StreamWrapper::getResource($stream);

        $this->stream = new Stream($resource);
        $this->stream->addFilter('zlib.inflate', STREAM_FILTER_READ);
    }

    /**
     * @param StreamInterface $stream
     *
     * @throws \RuntimeException
     *
     * @return int
     */
    private function getOffset(StreamInterface $stream)
    {
        // Default header size
        $offset = 10;

        if ($stream->isSeekable()) {
            $current = $stream->tell();
            $stream->seek(0);
            $data = $stream->read(10);
            $stream->seek($current);

            // Check for gzip header and calculate offset
            if (strpos($data, "\x1f\x8b\x08") === 0) {
                $flag = ord($data[3]);

                if ($flag > 0) {
                    if ($flag & 4) {
                        list($length) = unpack('v', substr($data, $offset, 2));
                        $offset = $offset + 2 + $length;
                    }
                    if ($flag & 8) {
                        $offset = strpos($data, "\0", $offset) + 1;
                    }
                    if ($flag & 16) {
                        $offset = strpos($data, "\0", $offset) + 1;
                    }
                    if ($flag & 2) {
                        $offset += 2;
                    }
                }
            }
        }

        return $offset;
    }
}