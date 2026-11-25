<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;
use Shared\Models\Move;

final class TmRequirementNotMetException extends DomainException
{
    public function __construct(Move $move, int $requiredLevel)
    {
        parent::__construct("Only level {$requiredLevel} Pokémon can learn {$move->tmName()}.");
    }

    public function getKey(): string
    {
        return 'move';
    }
}
