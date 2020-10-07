<?php

namespace Encount\Middleware;

use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Encount\Encount;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class EncountErrorHandlerMiddleware extends ErrorHandlerMiddleware
{
    public function handleException(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        $encount = new Encount();
        $encount->execute($exception);

        return parent::handleException($exception, $request);
    }
}
