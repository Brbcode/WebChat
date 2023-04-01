<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InvalidArgumentException extends \InvalidArgumentException implements IDomainException
{
    public function __construct(
        string $message = "",
        int $code = Response::HTTP_BAD_REQUEST,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }


    public static function build(
        string $message,
        int $code = Response::HTTP_BAD_REQUEST,
        ?Throwable $previous = null
    ): static {
        return new self($message, $code, $previous);
    }

    public static function buildFromArgumentName(string $argumentName): static
    {
        return static::build(
            sprintf("Invalid Argument '%s'.", $argumentName)
        );
    }
}
