<?php

declare(strict_types=1);

namespace App\Pokemon\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class PokemonCaught implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public string $pokemonId,
        public string $userId,
    ) {}
}
