<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;
use Shared\Models\Move;
use Shared\Models\Pokemon;

final class MoveAlreadyKnownException extends DomainException
{
    public function __construct(Pokemon $pokemon, Move $move)
    {
        parent::__construct("{$pokemon->getDisplayName()} already knows {$move->name}.");
    }

    public function getKey(): string
    {
        return 'move';
    }
}
