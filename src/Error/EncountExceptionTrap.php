<?php
declare(strict_types=1);

namespace Encount\Error;

use Cake\Error\ExceptionTrap;
use Encount\Encount;
use Throwable;

class EncountExceptionTrap extends ExceptionTrap
{
    /**
     * @param \Throwable $exception
     * @return void
     */
    public function handleException(Throwable $exception): void
    {
        $encount = new Encount();
        $encount->execute($exception);

        parent::handleException($exception);
    }

    /**
     * @param int $code
     * @param string $description
     * @param string $file
     * @param int $line
     * @return void
     */
    public function handleFatalError(int $code, string $description, string $file, int $line): void
    {
        $encount = new Encount();
        $encount->execute($code, 'FatalError', $description, $file, $line);

        parent::handleFatalError($code, $description, $file, $line);
    }
}
