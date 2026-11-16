<?php

declare(strict_types=1);

namespace App\Pokemon\Support;

use Shared\ValueObjects\IncubatorIndex;

final class LockKeys
{
    public static function incubatorPurchase(string $userId): string
    {
        return "pokemon::lock:incubator-purchase:user-{$userId}";
    }

    public static function incubatorPlaceEgg(string $userId, IncubatorIndex $incubatorIndex): string
    {
        return "pokemon::lock:incubator-place-egg:user-{$userId}:incubator-{$incubatorIndex->value}";
    }

    public static function incubatorHatchEgg(string $userId, IncubatorIndex $incubatorIndex): string
    {
        return "pokemon::lock:incubator-hatch-egg:user-{$userId}:incubator-{$incubatorIndex->value}";
    }

    public static function incubatorCatchPokemon(string $userId, IncubatorIndex $incubatorIndex): string
    {
        return "pokemon::lock:incubator-catch-pokemon:user-{$userId}:incubator-{$incubatorIndex->value}";
    }

    public static function pokemonEvolution(string $pokemonId): string
    {
        return "pokemon::lock:evolution:pokemon-{$pokemonId}";
    }

    public static function pokemonActiveMoves(string $pokemonId): string
    {
        return "pokemon::lock:active-moves:pokemon-{$pokemonId}";
    }
}
