<?php

namespace Encount\Handler;

use Cake\Core\App;
use Cake\Core\Configure;

class EncountHandler
{
    /*protected $_defaultConfig = [
        'force' => false,
        'sender' => ['Encount.Mail'],
        'mail' => [
            'prefix' => '',
            'html' => true
        ]
    ];*/

    public function execute($code, $errorType, $description, $file, $line, $context = [])
    {
        //Error
        //$config = $this->config();
        $config = Configure::read('Error.encount');

        if ($config['force'] === false && $debug > 0) {
            return ;
        }

        foreach ($config['sender'] as $senderName) {
            $sender  = $this->generateSender($senderName);
            $sender->send($config, $code, $errorType, $description, $file, $line, $context);
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
