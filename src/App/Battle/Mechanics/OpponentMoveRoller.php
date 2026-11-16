<?php

declare(strict_types=1);

namespace App\Battle\Mechanics;

use Shared\Helpers\RandomHelper;

final class OpponentMoveRoller
{
    /**
     * @param  non-empty-list<string>  $usableMoveIds
     */
    public function rollMoveId(array $usableMoveIds): string
    {
        return RandomHelper::pick($usableMoveIds);
    }
}
