<?php
namespace Encount\Sender;

use Cake\Mailer\Email;
use Cake\I18n\Time;
use Cake\Routing\Router;
use Cake\Error\Debugger;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

use Encount\Utility\EncountCollector;

class Mail implements SenderInterface
{
    /**
     * send email
     *
     * @access public
     * @author sakuragawa
     */
    public function send($config, $code, $errorType, $description, $file, $line, $context)
    {
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

        $subject = $prefix . '['. $date . '][' . strtoupper($errorType) . '][' . EncountCollector::$url . '] ' . $description;
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
        $params = EncountCollector::$requestParams;
        $trace = EncountCollector::$trace;
        $session = EncountCollector::$session;
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
            '* URL       : ' . EncountCollector::$url,
            '* Client IP : ' . EncountCollector::$ip,
            '* Referer   : ' . EncountCollector::$referer,
            '* Parameters: ' . trim(print_r($params, true)),
            '* Cake root : ' . APP,
            '',
            '-------------------------------',
            'Environment:',
            '-------------------------------',
            '',
            trim(print_r(EncountCollector::$environment, true)),
            '',
            '-------------------------------',
            'Session:',
            '-------------------------------',
            '',
            trim(print_r(EncountCollector::$session, true)),
            '',
            '-------------------------------',
            'Cookie:',
            '-------------------------------',
            '',
            trim(print_r(EncountCollector::$cookie, true)),
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
        $params = EncountCollector::$requestParams;
        $trace = EncountCollector::$trace;
        $session = EncountCollector::$session;
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
            EncountCollector::$url,
            '<h3>Client IP</h3>',
            EncountCollector::$ip,
            '<h3>Referer</h3>',
            EncountCollector::$referer,
            '<h3>Parameters</h3>',
            self::dumper($params),
            '<h3>Cake root</h3>',
            APP,
            '',
            '<h2>',
            'Environment:',
            '</h2>',
            '',
            self::dumper(EncountCollector::$environment),
            '',
            '<h2>',
            'Session:',
            '</h2>',
            '',
            self::dumper(EncountCollector::$session),
            '',
            '<h2>',
            'Cookie:',
            '</h2>',
            '',
            self::dumper(EncountCollector::$cookie),
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
}
