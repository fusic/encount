<?php
namespace Encount\Sender;

interface SenderInterface
{
    public function send($config, $collector);
}
