<?php

namespace App\EventListener;

use Throwable;

interface IExceptionListener
{
    public function generateJSON(Throwable $throwable): array;
}
