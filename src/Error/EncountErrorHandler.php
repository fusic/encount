<?php

namespace Encount\Error;

use Cake\Error\ErrorHandler;
use Encount\Encount;
use Exception;

class EncountErrorHandler extends ErrorHandler
{
    /**
     * Encount error handler
     *
     * @access public
     * @author sakuragawa
     */
    public function handleError($code, $description, $file = null, $line = null, $context = null)
    {
        $encount = new Encount();
        $encount->execute($code, $description, $file, $line, $context);

        return parent::handleError($code, $description, $file, $line, $context);
    }

    /**
     * Encount exception handler
     *
     * @access public
     * @author sakuragawa
     */
    public function handleException(Exception $exception)
    {
        $encount = new Encount();
        $encount->execute($exception);

        parent::handleException($exception);
    }

    /**
     * Encount fatal error handler
     *
     * @access public
     * @author sakuragawa
     */
    public function handleFatalError($code, $description, $file, $line)
    {
        $encount = new Encount();
        $encount->execute($code, 'FatalError', $description, $file, $line);

        return parent::handleFatalError($code, $description, $file, $line);
    }
}
