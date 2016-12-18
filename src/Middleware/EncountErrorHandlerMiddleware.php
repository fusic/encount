<?php

namespace Encount\Middleware;

use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Encount\Encount;

class EncountErrorHandlerMiddleware extends ErrorHandlerMiddleware
{
    public function handleException($exception, $request, $response)
    {
        $encount = new Encount($exception);
        $exceptionName = get_class($exception);

        $encount->execute($exception);
        return parent::handleException($exception, $request, $response);
    }
}
