<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;
use Shared\Models\Move;
use Shared\Models\Pokemon;

final class MoveNotKnownException extends DomainException
{
    public function __construct(Pokemon $pokemon, Move $move)
    {
        parent::__construct("{$pokemon->getDisplayName()} does not know {$move->name}.");
    }

    public function getKey(): string
    {
        return 'move';
    }
}
