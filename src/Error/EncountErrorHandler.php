<?php

namespace Encount\Error;

use Cake\Error\ErrorHandler;
use Cake\Core\InstanceConfigTrait;
use Cake\Core\Configure;

use Exception;

class EncountErrorHandler extends ErrorHandler
{
    use InstanceConfigTrait;

    protected $_defaultConfig = [
        'force' => false,
        'sender' => ['\Encount\Sender\Mail'],
        'mail' => [
            'prefix' => '',
            'html' => true
        ]
    ];

    public function __construct($options = [])
    {
        parent::__construct($options);

        $EncountConfig = [];
        if (isset($options['encount'])) {
            $EncountConfig = $options['encount'];
        }
        $this->config($EncountConfig);
    }

    /**
     * Encount error handler
     *
     * @access public
     * @author sakuragawa
     */
    public function handleError($code, $description, $file = null, $line = null, $context = null)
    {
        $errorCode = EncountErrorHandler::mapErrorCode($code);
        $errorType = $errorCode[0];

        $this->execute($code, $errorType, $description, $file, $line, $context);

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
        $exceptionName = get_class($exception);
        $this->execute($exception->getCode(), $exceptionName, $exception->getMessage(), $exception->getFile(), $exception->getLine());

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
        $errorCode = EncountErrorHandler::mapErrorCode($code);
        $errorStr = $errorCode[0];

        $this->execute($code, 'FatalError', $description, $file, $line);

        return parent::handleFatalError($code, $description, $file, $line);
    }

    /**
     * execute the sender
     *
     * @access public
     * @author sakuragawa
     */
    private function execute($code, $errorType, $description, $file, $line, $context = [])
    {
        $config = $this->config();
        $debug = Configure::read('debug');

        if ($config['force'] === false && $debug > 0) {
            return ;
        }

        foreach ($config['sender'] as $sender) {
            (new $sender())->send($config, $code, $errorType, $description, $file, $line, $context);
        }
    }
}
