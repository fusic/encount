<?php

namespace Encount\Utility;

use Cake\Routing\Router;
use Cake\Error\Debugger;

class EncountCollector
{
    static $url;
    static $ip;
    static $referer;
    static $requestParams;
    static $trace;
    static $session;
    static $environment;
    static $cookie;

    /**
     * collectNow
     *
     * @access public
     */
    public static function collectNow()
    {
        self::$url = self::url();
        self::$ip = self::ip();
        self::$referer = env('HTTP_REFERER');
        self::$requestParams = Router::getRequest();
        self::$trace = Debugger::trace(array('start' => 2, 'format' => 'base'));
        self::$session = isset($_SESSION) ? $_SESSION : array();
        self::$environment = $_SERVER;
        self::$cookie = $_COOKIE;
    }

    /**
     * get the url
     *
     * @access public
     * @author sakuragawa
     */
    public static function url()
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
    public static function ip()
    {
        $safe = true;
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
}
