<?php

namespace Encount\Middleware;

use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Encount\Handler\EncountHandler;

class EncountErrorHandlerMiddleware extends ErrorHandlerMiddleware
{
    public function handleException($exception, $request, $response)
    {
        $encount = new EncountHandler();
        $exceptionName = get_class($exception);
        $encount->execute($exception->getCode(), $exceptionName, $exception->getMessage(), $exception->getFile(), $exception->getLine());
        return parent::handleException($exception, $request, $response);
    }
}
