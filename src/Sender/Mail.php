<?php
declare(strict_types=1);

namespace Encount\Sender;

use Cake\I18n\Time;
use Cake\Mailer\Mailer;
use Encount\Collector\EncountCollector;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class Mail implements SenderInterface
{
    /**
     * @param array $config
     * @param \Encount\Collector\EncountCollector $collector
     * @return void
     */
    public function send(array $config, EncountCollector $collector): void
    {
        $subject = $this->subject($config, $collector);
        $body = $this->body($config, $collector);

        $format = 'text';
        if ($config['mail']['html'] === true) {
            $format = 'html';
        }

        $email = new Mailer('error');
        $email
            ->setEmailFormat($format)
            ->setSubject($subject)
            ->deliver($body);
    }

    /**
     * @param array $config
     * @param \Encount\Collector\EncountCollector $collector
     * @return string
     */
    private function subject(array $config, EncountCollector $collector): string
    {
        $prefix = $config['mail']['prefix'];
        $date = Time::now()->format('Ymd H:i:s');

        $subject = $prefix . '[' . $date . '][' . strtoupper($collector->errorType) . '][' . $collector->url . '] ' . $collector->description;

        return $subject;
    }

    /**
     * @param array $config
     * @param \Encount\Collector\EncountCollector $collector
     * @return string
     */
    private function body(array $config, EncountCollector $collector): string
    {
        $html = $config['mail']['html'];
        if ($html === true) {
            return self::getHtml($collector);
        }

        return self::getText($collector);
    }

    /**
     * @param \Encount\Collector\EncountCollector $collector
     * @return string
     */
    private function getText(EncountCollector $collector): string
    {
        $message = $collector->description;
        $params = $collector->requestParams;
        $trace = $collector->trace;
        $session = $collector->session;
        $file = $collector->file;
        $line = $collector->line;
        $context = $collector->context;

        $msg = [
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
            '* URL       : ' . $collector->url,
            '* Client IP : ' . $collector->ip,
            '* Referer   : ' . $collector->referer,
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
        ];

        return join("\n", $msg);
    }

    /**
     * @param \Encount\Collector\EncountCollector $collector
     * @return string
     */
    private function getHtml(EncountCollector $collector): string
    {
        $message = $collector->description;
        $params = $collector->requestParams;
        $trace = $collector->trace;
        $session = $collector->session;
        $file = $collector->file;
        $line = $collector->line;
        $context = $collector->context;

        $msg = [
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
            $collector->url,
            '<h3>Client IP</h3>',
            $collector->ip,
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
        ];

        return join('', $msg);
    }

    /**
     * @param mixed $obj
     * @return string|bool
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    private function dumper($obj): string|bool
    {
        ob_start();
        $cloner = new VarCloner();
        $dumper = new HtmlDumper();
        $handler = function ($obj) use ($cloner, $dumper) {
            return $dumper->dump($cloner->cloneVar($obj));
        };
        call_user_func($handler, $obj);
        $ret = ob_get_contents();
        ob_end_clean();

        return $ret;
    }
}
