<?php
declare(strict_types=1);

namespace Encount\Sender;

use Encount\Collector\EncountCollector;

interface SenderInterface
{
    /**
     * @param array $config
     * @param \Encount\Collector\EncountCollector $collector
     * @return void
     */
    public function send(array $config, EncountCollector $collector): void;
}
