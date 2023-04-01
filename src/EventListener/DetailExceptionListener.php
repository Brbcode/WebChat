<?php

namespace App\EventListener;

use Throwable;

class DetailExceptionListener extends ProdExceptionListener
{
    public function generateJSON(Throwable $throwable): array
    {
        $json = parent::generateJSON($throwable);

        $trace_count = count($throwable->getTrace());
        $count = isset($_ENV['MAXIMUM_TRACE_COUNT'])
            ?min($_ENV['MAXIMUM_TRACE_COUNT'], $trace_count)
            :$trace_count
        ;

        $json['environment'] = $_SERVER['APP_ENV'];
        $json['detail'] = [
            'trace' => array_slice($throwable->getTrace(), 0, $count),
            'type' => $throwable::class,
            'message' => $throwable->getMessage(),
            'code' => $throwable->getCode(),
        ];

        return $json;
    }
}
