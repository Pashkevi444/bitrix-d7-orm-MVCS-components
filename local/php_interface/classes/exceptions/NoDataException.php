<?php

namespace Exceptions;

use RuntimeException;
use Throwable;

/**
 * Exception that does not interrupt page execution.
 */
class NoDataException extends RuntimeException
{
    /**
     * Constructs a new NoDataException instance.
     *
     * @param string $message Error message.
     * @param int $code Error code (default is 0).
     * @param Throwable|null $previous Previous exception (default is null).
     */
    public function __construct(string $message = "No data available", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Converts the exception to a string representation.
     *
     * @return string Formatted exception message.
     */
    public function __toString(): string
    {
        return sprintf(
            "%s: [Code %d]: %s",
            static::class,
            $this->code,
            $this->message
        );
    }
}
