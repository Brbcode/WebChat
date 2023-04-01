<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Exception extends \Exception implements IDomainException
{

    public static function build(string $message, int $code, ?Throwable $previous = null): static
    {
        return new self($message, $code, $previous);
    }
}
