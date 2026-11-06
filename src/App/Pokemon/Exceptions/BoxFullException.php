<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;

final class BoxFullException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The box is full. There\'s no room for another Pokémon!');
    }

    public function getKey(): string
    {
        return 'box';
    }
}
