<?php
declare(strict_types=1);

namespace Encount\Collector;

use Cake\Error\Debugger;
use Cake\Error\PhpError;
use Cake\Routing\Router;
use Throwable;

class EncountCollector
{
    // phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
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
    // @phpcs:enable

    /**
     * @param mixed $code
     * @param mixed $description
     * @param mixed $file
     * @param mixed $line
     * @param mixed $context
     * @return void
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function build($code, $description, $file, $line, $context): void
    {
        if ($code instanceof Throwable) {
            $exception = $code;

            $code = $exception->getCode();
            $errorType = get_class($exception);
            $description = $exception->getMessage();
            $file = $exception->getFile();
            $line = $exception->getLine();
            $trace = Debugger::formatTrace($exception, ['format' => 'text']);
        } else {
            $errorType = (new PhpError($code, ''))->getLabel();
            $trace = Debugger::trace(['format' => 'text', 'start' => 3]);
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
            return;
        }

        $this->url = $this->url();
        $this->ip = $this->ip();
        $this->referer = env('HTTP_REFERER');
        $this->session = $_SESSION ?? [];
        $this->environment = $_SERVER;
        $this->cookie = $_COOKIE;

        $this->requestParams = [];
        $request = Router::getRequest();
        if (!is_null($request)) {
            $this->requestParams = $request->getAttributes();
        }
    }

    /**
     * @return mixed
     */
    public function url(): string
    {
        if (PHP_SAPI == 'cli') {
            return 'cli';
        }
        $protocol = array_key_exists('HTTPS', $_SERVER) ? 'https' : 'http';

        return $protocol . '://' . env('HTTP_HOST') . env('REQUEST_URI');
    }

    /**
     * @param bool $safe
     * @return string
     */
    public function ip(bool $safe = true): string
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
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'url' => $this->url,
            'ip' => $this->ip,
            'referer' => $this->referer,
            'requestParams' => $this->requestParams,
            'trace' => $this->trace,
            'session' => $this->session,
            'environment' => $this->environment,
            'cookie' => $this->cookie,
        ];
    }
}
