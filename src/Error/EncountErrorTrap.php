<?php
declare(strict_types=1);

namespace Encount\Error;

use Cake\Error\ErrorTrap;
use Encount\Encount;

class EncountErrorTrap extends ErrorTrap
{
    /**
     * @param int $code
     * @param string $description
     * @param string|null $file
     * @param int|null $line
     * @param array|null $context
     * @return bool
     */
    public function handleError(int $code, string $description, ?string $file = null, ?int $line = null, ?array $context = null): bool
    {
        $encount = new Encount();
        $encount->execute($code, $description, $file, $line, $context);

        return parent::handleError($code, $description, $file, $line, $context);
    }
}
