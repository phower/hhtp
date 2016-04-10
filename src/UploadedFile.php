<?php

namespace Phower\Http;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Value object representing a file uploaded through an HTTP request.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 */
class UploadedFile implements UploadedFileInterface
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $error;

    /**
     * @var null|string
     */
    private $file;

    /**
     * @var bool
     */
    private $moved = false;

    /**
     * @var int
     */
    private $size;

    /**
     * @var null|StreamInterface
     */
    private $stream;

    /**
     * Class constructor
     *
     * @param string|resource|\Phower\Http\StreamInterface $source
     * @param int $size
     * @param int $error
     * @param string|null $name
     * @param string|null $type
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($source, $size, $error, $name = null, $type = null)
    {
        if (is_string($source)) {
            $this->file = $source;
        } elseif (is_resource($source)) {
            $this->stream = new Stream($source);
        }

        if (!$this->file && !$this->stream) {
            if (!$source instanceof StreamInterface) {
                $message = sprintf('Invalid stream or file provided for "%s".', __METHOD__);
                throw new Exception\InvalidArgumentException($message);
            }
            $this->stream = $source;
        }

        if (!is_int($size)) {
            $message = sprintf('Invalid size provided for "%s"; must be an integer.', __METHOD__);
            throw new Exception\InvalidArgumentException($message);
        }
        $this->size = $size;

        if (!is_int($error) || $error < 0 || $error > 8) {
            $message = sprintf('Invalid error status for "%s"; must be an UPLOAD_ERR_* constants.', __METHOD__);
            throw new Exception\InvalidArgumentException($message);
        }
        $this->error = $error;

        if (null !== $name && !is_string($name)) {
            $message = sprintf('Invalid client filename provided for "%s"; must be null or a string.', __METHOD__);
            throw new Exception\InvalidArgumentException($message);
        }
        $this->name = $name;

        if (null !== $type && !is_string($type)) {
            $message = sprintf('Invalid client media type provided for "%s"; must be null or a string', __METHOD__);
            throw new Exception\InvalidArgumentException($message);
        }
        $this->type = $type;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the moveTo() method has been called previously, this method MUST raise
     * an exception.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     * @throws \RuntimeException in cases when no stream is available or can be
     *     created.
     */
    public function getStream()
    {
        if ($this->moved) {
            $message = 'Cannot retrieve stream after it has already been moved.';
            throw new Exception\RuntimeException($message);
        }

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        $this->stream = new Stream($this->file);
        return $this->stream;
    }

    /**
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file or stream MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see    http://php.net/is_uploaded_file
     * @see    http://php.net/move_uploaded_file
     * @param  string $targetPath Path to which to move the uploaded file.
     * @throws \InvalidArgumentException if the $path specified is invalid.
     * @throws \RuntimeException on any error during the move operation, or on
     *     the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        if (!is_string($targetPath)) {
            $message = 'Invalid path provided for move operation; must be a string.';
            throw new Exception\InvalidArgumentException($message);
        }

        if (empty($targetPath)) {
            $message = 'Invalid path provided for move operation; must be a non-empty string.';
            throw new Exception\InvalidArgumentException($message);
        }

        if ($this->moved) {
            $message = 'Cannot move file; already moved!.';
            throw new Exception\RuntimeException($message);
        }

        if (!$this->file || false === move_uploaded_file($this->file, $targetPath)) {
            $this->writeFile($targetPath);
            if ($this->file && is_writable($this->file)) {
                unlink($this->file);
            }
        }

        $this->moved = true;
    }

    /**
     * Retrieve the file size.
     *
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     *
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see    http://php.net/manual/en/features.file-upload.errors.php
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename()
    {
        return $this->name;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return string|null The media type sent by the client or null if none
     *     was provided.
     */
    public function getClientMediaType()
    {
        return $this->type;
    }

    /**
     * Write internal stream to given path
     *
     * @param string $path
     */
    private function writeFile($path)
    {
        if (false === $handle = fopen($path, 'wb+')) {
            $message = sprintf('Unable to write to "%s".', $path);
            throw new Exception\RuntimeException($message);
        }

        $stream = $this->getStream();
        $stream->rewind();

        while (!$stream->eof()) {
            fwrite($handle, $this->stream->read(4096));
        }

        fclose($handle);
    }
}
