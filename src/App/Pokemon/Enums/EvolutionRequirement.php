<?php

declare(strict_types=1);

namespace App\Pokemon\Enums;

use LogicException;
use Shared\Models\SpeciesEvolution;

enum EvolutionRequirement: string
{
    case GENDER = 'gender';
    case LEVEL  = 'level';
    case ITEM   = 'item';

    public function reason(SpeciesEvolution $evolution): string
    {
        return match ($this) {
            self::GENDER => 'Needs a ' . ($evolution->gender->value ?? throw new LogicException('The evolution has no gender requirement.')) . ' Pokémon',
            self::LEVEL  => 'Needs level ' . ($evolution->min_level ?? throw new LogicException('The evolution has no level requirement.')),
            self::ITEM   => 'Needs ' . $this->withArticle($evolution->evolutionItem->name ?? throw new LogicException('The evolution has no item requirement.')),
        };
    }

    private function withArticle(string $name): string
    {
        $article = in_array(strtolower($name[0]), ['a', 'e', 'i', 'o', 'u'], true) ? 'an' : 'a';

        return "{$article} {$name}";
    }
}
