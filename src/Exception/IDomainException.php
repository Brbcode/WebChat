<?php

namespace App\Exception;

use Throwable;

interface IDomainException extends Throwable
{
    public static function build(string $message, int $code, ?Throwable $previous = null): static;
}
