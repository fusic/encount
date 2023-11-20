<?php
declare(strict_types=1);

namespace Encount;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Encount\Collector\EncountCollector;
use Exception;
use InvalidArgumentException;

class Encount
{
    use InstanceConfigTrait;

    /**
     * @var array
     */
    protected array $_defaultConfig = [
        'force' => false,
        'sender' => ['Encount.Mail'],
        'deny' => [
            'error' => [],
            'exception' => [],
        ],
        'mail' => [
            'prefix' => '',
            'html' => true,
        ],
    ];

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $config = Configure::read('Error.encount');

        $encountConfig = [];
        if (!empty($config)) {
            $encountConfig = $config;
        }

        $this->setConfig($encountConfig, null, false);
    }

    /**
     * @param mixed $code
     * @param mixed $description
     * @param mixed $file
     * @param mixed $line
     * @param mixed $context
     * @return void
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function execute($code, $description = null, $file = null, $line = null, $context = null): void
    {
        $debug = Configure::read('debug');

        if ($this->getConfig('force') === false && $debug == true) {
            return;
        }

        if ($this->deny($code)) {
            return;
        }

        $collector = new EncountCollector();
        $collector->build($code, $description, $file, $line, $context);

        foreach ($this->getConfig('sender') as $senderName) {
            $sender = $this->generateSender($senderName);
            $sender->send($this->getConfig(), $collector);
        }
    }

    /**
     * @param mixed $check
     * @return bool
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    private function deny($check): bool
    {
        $denyList = $this->getConfig('deny');

        if ($check instanceof Exception) {
            if (isset($denyList['exception'])) {
                foreach ($denyList['exception'] as $ex) {
                    if (is_a($check, $ex)) {
                        return true;
                    }
                }
            }
        } else {
            if (isset($denyList['error'])) {
                foreach ($denyList['error'] as $e) {
                    if ($check == $e) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param string $name
     * @return object
     * @throws \InvalidArgumentException
     */
    private function generateSender(string $name): object
    {
        $class = App::className($name, 'Sender');
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Encount sender "%s" was not found.', $class));
        }

        return new $class();
    }
}
