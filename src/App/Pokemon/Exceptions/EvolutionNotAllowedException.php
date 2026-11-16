<?php

declare(strict_types=1);

namespace App\Pokemon\Exceptions;

use App\Pokemon\Enums\EvolutionRequirement;
use Shared\Base\DomainException;
use Shared\Models\SpeciesEvolution;

final class EvolutionNotAllowedException extends DomainException
{
    public function __construct(EvolutionRequirement $unmetRequirement, SpeciesEvolution $evolution)
    {
        parent::__construct("This Pokémon can't evolve yet. {$unmetRequirement->reason($evolution)}.");
    }

    public function getKey(): string
    {
        return 'evolution';
    }
}
