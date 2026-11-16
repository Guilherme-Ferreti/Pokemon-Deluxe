<?php

declare(strict_types=1);

namespace App\Battle\Mechanics;

use Shared\Enums\Ailment;
use Shared\Enums\PokemonType;
use Shared\ValueObjects\Combatant;

final class AilmentInflictionChecker
{
    public function canInflict(Ailment $ailment, Combatant $target): bool
    {
        if ($target->isFainted() || $ailment === Ailment::UNKNOWN) {
            return false;
        }

        if ($ailment === Ailment::CONFUSION) {
            return $target->confusion_turns_left === 0;
        }

        if ($target->ailment instanceof Ailment) {
            return false;
        }

        $immuneTypes = $this->immuneTypes($ailment);

        return ! array_any($target->types, fn (PokemonType $type) => in_array($type, $immuneTypes, true));
    }

    /**
     * @return list<PokemonType>
     */
    private function immuneTypes(Ailment $ailment): array
    {
        return match ($ailment) {
            Ailment::BURN      => [PokemonType::FIRE],
            Ailment::FREEZE    => [PokemonType::ICE],
            Ailment::PARALYSIS => [PokemonType::ELECTRIC],
            Ailment::POISON    => [PokemonType::POISON, PokemonType::STEEL],
            default            => [],
        };
    }
}
