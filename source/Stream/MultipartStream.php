<?php

namespace ic\Framework\Stream;

/**
 * Class MultipartStream
 *
 * @package ic\Framework\Stream
 */
class MultipartStream implements StreamInterface
{

    use StreamDecorator;

    /**
     * @var string
     */
    private $boundary;

    /**
     * @param array  $elements Array of associative arrays, each containing a
     *                         required "name" key mapping to the form field,
     *                         name, a required "contents" key mapping to a
     *                         StreamInterface/resource/string, an optional
     *                         "headers" associative array of custom headers,
     *                         and an optional "filename" key mapping to a
     *                         string to send as the filename in the part.
     * @param string $boundary You can optionally provide a specific boundary
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $elements = [], $boundary = null)
    {
        $this->boundary = $boundary ?: uniqid($_SERVER['SERVER_ADDR'], true);
        $this->stream   = $this->createStream($elements);
    }

    /**
     * Get the boundary
     *
     * @return string
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * @inheritdoc
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * Get the headers needed before transferring the content of a POST file
     *
     * @param array $headers
     *
     * @return string
     */
    private function getHeaders(array $headers)
    {
        $str = '';
        foreach ($headers as $key => $value) {
            $str .= "{$key}: {$value}\r\n";
        }

        return "--{$this->boundary}\r\n" . trim($str) . "\r\n\r\n";
    }

    /**
     * Create the aggregate stream that will be used to upload the POST data
     *
     * @param array $elements
     *
     * @throws \InvalidArgumentException
     *
     * @return AppendStream
     */
    protected function createStream(array $elements)
    {
        $stream = new AppendStream();

        foreach ($elements as $element) {
            $this->addElement($stream, $element);
        }

        // Add the trailing boundary with CRLF
        $stream->addStream(Stream::create("--{$this->boundary}--\r\n"));

        return $stream;
    }

    /**
     * @param AppendStream $stream
     * @param array        $element
     *
     * @throws \InvalidArgumentException
     */
    private function addElement(AppendStream $stream, array $element)
    {
        foreach (['contents', 'name'] as $key) {
            if (!array_key_exists($key, $element)) {
                throw new \InvalidArgumentException("A '{$key}' key is required");
            }
        }

        $element['contents'] = Stream::create($element['contents']);

        if (empty($element['filename'])) {
            $uri = $element['contents']->getMetadata('uri');
            if (strpos($uri, 'php://') !== 0) {
                $element['filename'] = $uri;
            }
        }

        list($body, $headers) = $this->createElement(
            $element['name'],
            $element['contents'],
            isset($element['filename']) ? $element['filename'] : null,
            isset($element['headers']) ? $element['headers'] : []
        );

        $stream->addStream(Stream::create($this->getHeaders($headers)));
        $stream->addStream($body);
        $stream->addStream(Stream::create("\r\n"));
    }

    /**
     * @param string          $name
     * @param StreamInterface $stream
     * @param string          $filename
     * @param array           $headers
     *
     * @return array
     */
    private function createElement($name, StreamInterface $stream, $filename, array $headers)
    {
        // Set a default content-disposition header if one was no provided
        $disposition = $this->getHeader($headers, 'content-disposition');
        if (!$disposition) {
            $headers['Content-Disposition'] = ($filename === '0' || $filename)
                ? sprintf('form-data; name="%s"; filename="%s"', $name, basename($filename))
                : "form-data; name=\"{$name}\"";
        }

        // Set a default content-length header if one was no provided
        $length = $this->getHeader($headers, 'content-length');
        if (!$length && ($length = $stream->getSize())) {
            $headers['Content-Length'] = (string)$length;
        }

        // Set a default Content-Type if one was not supplied
        $type = $this->getHeader($headers, 'content-type');
        if (!$type && ($filename === '0' || $filename) && ($type = MimeType::fromFilename($filename))) {
            $headers['Content-Type'] = $type;
        }

        return [$stream, $headers];
    }

    /**
     * @param array  $headers
     * @param string $name
     *
     * @return mixed|null
     */
    private function getHeader(array $headers, $name)
    {
        $lowercaseHeader = strtolower($name);
        foreach ($headers as $header => $value) {
            if (strtolower($header) === $lowercaseHeader) {
                return $value;
            }
        }

        return null;
    }
}