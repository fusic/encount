<?php

namespace Encount\Collector;

use Cake\Routing\Router;
use Cake\Error\Debugger;
use Cake\Error\BaseErrorHandler;
use Exception;

class EncountCollector
{
    public $url;
    public $ip;
    public $referer;
    public $requestParams;
    public $trace;
    public $session;
    public $environment;
    public $cookie;

    public $code;
    public $errorType;
    public $description;
    public $file;
    public $line;
    public $context;

    /**
     * build
     *
     * @access public
     */
    public function build($code, $description, $file, $line, $context)
    {

        if ($code instanceof Exception) {
            $exception = $code;

            $code = $exception->getCode();
            $errorType = get_class($exception);
            $description = $exception->getMessage();
            $file = $exception->getFile();
            $line = $exception->getLine();
            $trace = Debugger::formatTrace($exception, ['format' => 'base']);
        } else {
            $errorCode = BaseErrorHandler::mapErrorCode($code);
            $errorType = $errorCode[0];
            $trace = Debugger::trace(['format' => 'base', 'start' => 3]);
        }
        $this->code = $code;
        $this->errorType = $errorType;
        $this->description = $description;
        $this->file = $file;
        $this->line = $line;
        $this->context = $context;
        $this->trace = $trace;

        $isCli = PHP_SAPI === 'cli';
        if ($isCli) {
            return ;
        }

        $this->url = $this->url();
        $this->ip = $this->ip();
        $this->referer = env('HTTP_REFERER');
        $this->requestParams = Router::getRequest()->params;
        $this->session = isset($_SESSION) ? $_SESSION : array();
        $this->environment = $_SERVER;
        $this->cookie = $_COOKIE;
    }

    /**
     * get the url
     *
     * @access public
     * @author sakuragawa
     */
    public function url()
    {
        if (PHP_SAPI == 'cli') {
            return 'cli';
        }
        $protocol = array_key_exists('HTTPS', $_SERVER) ? 'https' : 'http';
        return $protocol . '://' . env('HTTP_HOST') . env('REQUEST_URI');
    }

    /**
     * get the client IP
     *
     * @access public
     * @author sakuragawa
     */
    public function ip($safe=true)
    {
        if (!$safe && env('HTTP_X_FORWARDED_FOR')) {
            $env = 'HTTP_X_FORWARDED_FOR';
            $ipaddr = preg_replace('/(?:,.*)/', '', env('HTTP_X_FORWARDED_FOR'));
        } else {
            if (env('HTTP_CLIENT_IP')) {
                $env = 'HTTP_CLIENT_IP';
                $ipaddr = env('HTTP_CLIENT_IP');
            } else {
                $env = 'REMOTE_ADDR';
                $ipaddr = env('REMOTE_ADDR');
            }
        }
        if (env('HTTP_CLIENTADDRESS')) {
            $tmpipaddr = env('HTTP_CLIENTADDRESS');
            if (!empty($tmpipaddr)) {
                $env = 'HTTP_CLIENTADDRESS';
                $ipaddr = preg_replace('/(?:,.*)/', '', $tmpipaddr);
            }
        }
        return trim($ipaddr) . ' [' . $env . ']';
    }

    /**
     * Returns an array
     *
     * @access public
     * @author sakuragawa
     */
    public function __debugInfo() {
        return [
            'url' => $this->url,
            'ip' => $this->ip,
            'referer' => $this->referer,
            'requestParams' => $this->requestParams,
            'trace' => $this->trace,
            'session' => $this->session,
            'environment' => $this->environment,
            'cookie' => $this->cookie
        ];
    }
}
