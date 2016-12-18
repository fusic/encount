<?php
namespace Encount\Sender;

use Cake\Mailer\Email;
use Cake\I18n\Time;
use Cake\Routing\Router;
use Cake\Error\Debugger;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class Mail implements SenderInterface
{
    /**
     * send email
     *
     * @access public
     * @author sakuragawa
     */
    public function send($config, $collector)
    {
        debug("send mail.");exit;
        $subject = $this->subject($config, $errorType, $description);
        $body = $this->body($config, $description, $file, $file, $context);

        $format = 'text';
        if ($config['mail']['html'] === true) {
            $format = 'html';
        }

        $email = new Email('error');
        $email
            ->emailFormat($format)
            ->subject($subject)
            ->send($body);
    }

    /**
     * generate subject
     *
     * @access private
     * @author sakuragawa
     */
    private function subject($config, $errorType, $description)
    {
        $prefix = $config['mail']['prefix'];
        $date = Time::now()->format('Ymd H:i:s');

        $subject = $prefix . '['. $date . '][' . strtoupper($errorType) . '][' . $this->url() . '] ' . $description;
        return $subject;
    }

    /**
     * generate body
     *
     * @access private
     * @author sakuragawa
     */
    private function body($config, $message, $file, $line, $context = null){
        $html = $config['mail']['html'];
        if ($html === true) {
            return self::getHtml($message, $file, $line, $context);
        } else {
            return self::getText($message, $file, $line, $context);
        }
    }

    /**
     * get the body for text message
     *
     * @access private
     * @author sakuragawa
     */
    private function getText($message, $file, $line, $context = null){
        $params = Router::getRequest();
        // $trace = Debugger::trace(array('start' => 2, 'format' => 'base'));
        $trace = Debugger::trace(array('format' => 'base'));
        $session = isset($_SESSION) ? $_SESSION : array();
        $msg = array(
            $message,
            $file . '(' . $line . ')',
            '',
            '-------------------------------',
            'Backtrace:',
            '-------------------------------',
            '',
            trim(print_r($trace, true)),
            '',
            '-------------------------------',
            'Request:',
            '-------------------------------',
            '',
            '* URL       : ' . $this->url(),
            '* Client IP : ' . $this->getClientIp(),
            '* Referer   : ' . env('HTTP_REFERER'),
            '* Parameters: ' . trim(print_r($params, true)),
            '* Cake root : ' . APP,
            '',
            '-------------------------------',
            'Environment:',
            '-------------------------------',
            '',
            trim(print_r($_SERVER, true)),
            '',
            '-------------------------------',
            'Session:',
            '-------------------------------',
            '',
            trim(print_r($session, true)),
            '',
            '-------------------------------',
            'Cookie:',
            '-------------------------------',
            '',
            trim(print_r($_COOKIE, true)),
            '',
            '-------------------------------',
            'Context:',
            '-------------------------------',
            '',
            trim(print_r($context, true)),
            '',
            );
        return join("\n", $msg);
    }

    /**
     * get the body for htmll message
     *
     * @access private
     * @author sakuragawa
     */
    private function getHtml($message, $file, $line, $context = null){
        $params = Router::getRequest();
        //$trace = Debugger::trace(array('start' => 2, 'format' => 'base'));
        $trace = Debugger::trace(array('format' => 'base'));
        $session = isset($_SESSION) ? $_SESSION : array();
        $msg = array(
            '<p><strong>',
            $message,
            '</strong></p>',
            '<p>',
            $file . '(' . $line . ')',
            '</p>',
            '',
            '<h2>',
            'Backtrace:',
            '</h2>',
            '',
            '<pre>',
            self::dumper($trace),
            '</pre>',
            '',
            '<h2>',
            'Request:',
            '</h2>',
            '',
            '<h3>URL</h3>',
            $this->url(),
            '<h3>Client IP</h3>',
            $this->getClientIp(),
            '<h3>Referer</h3>',
            env('HTTP_REFERER'),
            '<h3>Parameters</h3>',
            self::dumper($params),
            '<h3>Cake root</h3>',
            APP,
            '',
            '<h2>',
            'Environment:',
            '</h2>',
            '',
            self::dumper($_SERVER),
            '',
            '<h2>',
            'Session:',
            '</h2>',
            '',
            self::dumper($session),
            '',
            '<h2>',
            'Cookie:',
            '</h2>',
            '',
            self::dumper($_COOKIE),
            '',
            '<h2>',
            'Context:',
            '</h2>',
            '',
            self::dumper($context),
            '',
            );
        return join("", $msg);
    }


    /**
     * generate message
     *
     * @access private
     * @author sakuragawa
     */
    private function dumper($obj) {
        ob_start();
        $cloner = new VarCloner();
        $dumper = new HtmlDumper();
        $handler = function ($obj) use ($cloner, $dumper) {
            $dumper->dump($cloner->cloneVar($obj));
        };
        call_user_func($handler, $obj);
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }

    /**
     * get the url
     *
     * @access private
     * @author sakuragawa
     */
    private function url() {
        if (PHP_SAPI == 'cli') {
            return 'cli';
        }
        $protocol = array_key_exists('HTTPS', $_SERVER) ? 'https' : 'http';
        return $protocol . '://' . env('HTTP_HOST') . env('REQUEST_URI');
    }

    /**
     * get the client IP
     *
     * @access private
     * @author sakuragawa
     */
    private function getClientIp(){
        //$safe = Configure::read('ExceptionNotifier.clientIpSafe');
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
