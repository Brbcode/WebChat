<?php

namespace App\EventListener;

use App\Exception\IDomainException;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Throwable;

class ProdExceptionListener implements IExceptionListener
{
    public function generateJSON(Throwable $throwable): array
    {
        $json = [];
        if ($throwable instanceof IDomainException) {
            $json['message'] = $throwable->getMessage();
            $json['code'] = $throwable->getCode();
        } else {
            $json['message'] = 'Internal server error';
            $json['code'] = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $json;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        $json = $this->generateJSON($throwable);

        $response = new JsonResponse($json);
        $response->setStatusCode($json['code']);

        $event->setResponse($response);
    }
}
