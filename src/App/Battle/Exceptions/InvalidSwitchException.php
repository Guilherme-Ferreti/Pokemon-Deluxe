<?php

declare(strict_types=1);

namespace App\Battle\Exceptions;

use Shared\Base\DomainException;

final class InvalidSwitchException extends DomainException
{
    public static function notInTeam(): self
    {
        return new self("That Pokémon isn't in this battle.");
    }

    public static function alreadyInBattle(string $displayName): self
    {
        return new self("{$displayName} is already in battle.");
    }

    public static function fainted(string $displayName): self
    {
        return new self("{$displayName} has fainted and can't battle.");
    }

    public function getKey(): string
    {
        return 'pokemon';
    }
}
