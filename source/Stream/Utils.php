<?php

namespace ic\Framework\Stream;

/**
 * Class Utils
 *
 * @package ic\Framework\Stream
 */
class Utils
{

    private static $readModes = ['r', 'w+', 'r+', 'x+', 'c+', 'rb', 'w+b', 'r+b', 'x+b', 'c+b', 'rt', 'w+t', 'r+t', 'x+t', 'c+t', 'a+'];

    private static $writeModes = ['w', 'w+', 'rw', 'r+', 'x+', 'c+', 'wb', 'w+b', 'r+b', 'x+b', 'c+b', 'w+t', 'r+t', 'x+t', 'c+t', 'a', 'a+'];

    /**
     * @param string $mode
     *
     * @return bool
     */
    public static function isReadable($mode)
    {
        return in_array($mode, static::$readModes, false) !== false;
    }

    /**
     * @param string $mode
     *
     * @return bool
     */
    public static function isWritable($mode)
    {
        return in_array($mode, static::$writeModes, false) !== false;
    }

    /**
     * @param string $filename
     * @param string $mode
     * @param bool   $lazy
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @return StreamInterface
     */
    public static function file($filename, $mode, $lazy = false)
    {
        if ($lazy) {
            return new LazyOpenStream($filename, $mode);
        }

        return Stream::create(static::open($filename, $mode));
    }

    /**
     * Safely opens a PHP stream resource using a filename.
     *
     * When fopen fails, PHP normally raises a warning. This function adds an
     * error handler that checks for errors and throws an exception instead.
     *
     * @param string $filename File to open
     * @param string $mode     Mode used to open the file
     *
     * @return resource
     * @throws \RuntimeException if the file cannot be opened
     */
    public static function open($filename, $mode)
    {
        $exception = null;
        set_error_handler(function () use ($filename, $mode, &$exception) {
            $exception = new \RuntimeException(sprintf('Unable to open %s using mode %s: %s', $filename, $mode, func_get_args()[1]));
        });
        $handle = fopen($filename, $mode);
        restore_error_handler();

        if ($exception) {
            /** @var $exception \RuntimeException */
            throw $exception;
        }

        return $handle;
    }

    /**
     * Create a resource and check to ensure it was created successfully
     *
     * @param callable $callback Callable that returns stream resource
     *
     * @return resource
     * @throws \RuntimeException on error
     */
    public static function getSafeResource(callable $callback)
    {
        $errors = [];

        set_error_handler(function ($unused, $message, $file, $line) use (&$errors) {
            $errors[] = [
                'message' => $message,
                'file'    => $file,
                'line'    => $line,
            ];

            return true;
        });

        $resource = $callback();

        restore_error_handler();

        if (!$resource) {
            $message = 'Error creating resource: ';

            foreach ($errors as $error) {
                foreach ((array)$error as $key => $value) {
                    $message .= "[$key] $value" . PHP_EOL;
                }
            }

            throw new \RuntimeException(trim($message));
        }

        return $resource;
    }

    /**
     * @param resource|null $value
     *
     * @return null|resource
     */
    public static function getDebugResource($value = null)
    {
        if (is_resource($value)) {
            return $value;
        } elseif (defined('STDOUT')) {
            return STDOUT;
        }

        return fopen('php://output', 'wb');
    }

    /**
     * @return resource
     */
    public static function getTempResource()
    {
        return fopen('php://temp', 'w+b');
    }

    /**
     * Copy the contents of a stream into a string until the given number of
     * bytes have been read.
     *
     * @param StreamInterface $stream
     * @param int             $maxLength
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function copyToString(StreamInterface $stream, $maxLength = -1)
    {
        $string = '';

        if ($maxLength === -1) {
            while (!$stream->eof()) {
                $buffer = $stream->read(1048576);

                if ($buffer === false) {
                    break;
                }

                $string .= $buffer;
            }

            return $string;
        }

        $length = 0;

        while (!$stream->eof() && $length < $maxLength) {
            $buffer = $stream->read($maxLength - $length);

            if ($buffer === false) {
                break;
            }

            $string .= $buffer;
            $length = strlen($string);
        }

        return $string;
    }

    /**
     * Copy the contents of a stream into another stream until the given number
     * of bytes have been read.
     *
     * @param StreamInterface $source
     * @param StreamInterface $target
     * @param int             $maxLength
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function copyToStream(StreamInterface $source, StreamInterface $target, $maxLength = -1)
    {
        if ($maxLength === -1) {
            while (!$source->eof()) {
                if (!$target->write($source->read(1048576))) {
                    break;
                }
            }

            return;
        }

        $bytes = 0;

        while (!$source->eof()) {
            $buffer = $source->read($maxLength - $bytes);

            if (!($length = strlen($buffer))) {
                break;
            }

            $bytes += $length;
            $target->write($buffer);

            if ($bytes === $maxLength) {
                break;
            }
        }
    }

    /**
     * Calculate a hash of a Stream
     *
     * @param StreamInterface $stream    Stream to calculate the hash for
     * @param string          $algorithm Hash algorithm (e.g. md5, crc32, etc)
     * @param bool            $rawOutput Whether or not to use raw output
     *
     * @return string Returns the hash of the stream
     * @throws \RuntimeException
     */
    public static function hash(StreamInterface $stream, $algorithm, $rawOutput = false)
    {
        $position = $stream->tell();

        if ($position > 0 && !$stream->seek(0)) {
            throw new \RuntimeException('Cannot seek stream.');
        }

        $context = hash_init($algorithm);
        while (!$stream->eof()) {
            hash_update($context, $stream->read(1048576));
        }

        $hash = hash_final($context, (bool)$rawOutput);
        $stream->seek($position);

        return $hash;
    }

    /**
     * Read a line from the stream up to the maximum allowed buffer length
     *
     * @param StreamInterface $stream    Stream to read from
     * @param int             $maxLength Maximum buffer length
     * @param string          $eol       Line ending
     *
     * @throws \RuntimeException
     *
     * @return string|bool
     */
    public static function readline(StreamInterface $stream, $maxLength = null, $eol = PHP_EOL)
    {
        $buffer    = '';
        $size      = 0;
        $eolLength = -strlen($eol);

        while (!$stream->eof()) {
            if (false === ($byte = $stream->read(1))) {
                return $buffer;
            }

            $buffer .= $byte;

            // Break when a new line is found or the max length - 1 is reached
            if (++$size === $maxLength || substr($buffer, $eolLength) === $eol) {
                break;
            }
        }

        return $buffer;
    }

    /**
     * @param string $compressed
     *
     * @return bool|string
     */
    public static function inflate($compressed)
    {
        if (empty($compressed)) {
            return $compressed;
        }

        if (function_exists('gzinflate')) {
            if (false !== ($decompressed = @gzinflate($compressed))) {
                return $decompressed;
            }

            if (false !== ($decompressed = static::gzinflate($compressed))) {
                return $decompressed;
            }
        }

        if (function_exists('gzuncompress')) {
            if (false !== ($decompressed = @gzuncompress($compressed))) {
                return $decompressed;
            }
        }

        if (function_exists('gzdecode')) {
            if (false !== ($decompressed = @gzdecode($compressed))) {
                return $decompressed;
            }
        }

        return false;
    }

    /**
     * @param string $compressed
     *
     * @return bool|string
     */
    private static function gzinflate($compressed)
    {
        // Compressed data might contain a full header, if so strip it for gzinflate().
        if (strpos($compressed, "\x1f\x8b\x08") === 0) {
            $index = 10;
            $flag  = ord($compressed[3]);

            if ($flag > 0) {
                if ($flag & 4) {
                    list($length) = unpack('v', substr($compressed, $index, 2));
                    $index = $index + 2 + $length;
                }
                if ($flag & 8) {
                    $index = strpos($compressed, "\0", $index) + 1;
                }
                if ($flag & 16) {
                    $index = strpos($compressed, "\0", $index) + 1;
                }
                if ($flag & 2) {
                    $index += 2;
                }
            }

            if (false !== ($decompressed = @gzinflate(substr($compressed, $index, -8)))) {
                return $decompressed;
            }
        }

        // Compressed data from java.util.zip.Deflater amongst others.
        if (false !== ($decompressed = @gzinflate(substr($compressed, 2)))) {
            return $decompressed;
        }

        return false;
    }

}