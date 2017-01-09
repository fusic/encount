<?php

namespace Encount;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Encount\Collector\EncountCollector;

class Encount
{
    use InstanceConfigTrait;

    protected $_defaultConfig = [
        'force' => false,
        'sender' => ['Encount.Mail'],
        'mail' => [
            'prefix' => '',
            'html' => true
        ]
    ];

    public function __construct()
    {
        $config = Configure::read('Error.encount');

        $encountConfig = [];
        if (!empty($config)) {
            $encountConfig = $config;
        }

        $this->config($encountConfig, null, false);
    }

    public function execute($code, $errorType = null, $description = null, $file = null, $line = null, $context = null)
    {
        $debug = Configure::read('debug');

        if ($this->_config['force'] === false && $debug > 0) {
            return ;
        }

        $collector = new EncountCollector();
        $collector->build($code, $errorType, $description, $file, $line, $context);

        foreach ($this->_config['sender'] as $senderName) {
            $sender  = $this->generateSender($senderName);
            $sender->send($this->_config, $collector);
        }
    }

    /**
     * generate Encount Sender
     *
     * @access private
     * @author sakuragawa
     */
    private function generateSender($name)
    {
        $class = App::className($name, 'Sender');
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Encount sender "%s" was not found.', $class));
        }

        return new $class();
    }
}
