<?php

declare(strict_types=1);

namespace App\Battle\Exceptions;

use Shared\Base\DomainException;

final class NotAWildBattleException extends DomainException
{
    public function __construct()
    {
        parent::__construct('You can only throw a ball at a wild Pokémon.');
    }

    public function getKey(): string
    {
        return 'battle';
    }
}
