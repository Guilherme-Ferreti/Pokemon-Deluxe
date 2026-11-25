<?php

declare(strict_types=1);

namespace App\Pokemon\Actions;

use Shared\Models\Pokemon;

final class SetPokemonLockAction
{
    public function handle(Pokemon $pokemon, bool $isLocked): void
    {
        $pokemon->update(['is_locked' => $isLocked]);
    }
}
