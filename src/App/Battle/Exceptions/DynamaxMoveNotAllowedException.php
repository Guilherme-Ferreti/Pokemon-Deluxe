<?php

declare(strict_types=1);

namespace App\Battle\Exceptions;

use Shared\Base\DomainException;
use Shared\Models\Move;

final class DynamaxMoveNotAllowedException extends DomainException
{
    public function __construct(Move $move)
    {
        parent::__construct("Only G-Max Pokémon can use {$move->name}.");
    }

    public function getKey(): string
    {
        return 'move';
    }
}
