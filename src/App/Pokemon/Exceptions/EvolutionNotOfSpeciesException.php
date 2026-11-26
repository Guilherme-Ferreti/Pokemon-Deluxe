<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use Shared\Base\DomainException;
use Shared\Models\Pokemon;
use Shared\Models\SpeciesEvolution;

final class EvolutionNotOfSpeciesException extends DomainException
{
    public function __construct(Pokemon $pokemon, SpeciesEvolution $evolution)
    {
        parent::__construct("{$pokemon->getDisplayName()} can't evolve into {$evolution->into->getDisplayName($pokemon->is_shiny)}.");
    }

    public function getKey(): string
    {
        return 'evolution';
    }
}
