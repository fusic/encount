<?php
namespace Encount\Sender;

interface SenderInterface
{
    public function send($config, $code, $errorType, $description, $file, $line, $context);
}
