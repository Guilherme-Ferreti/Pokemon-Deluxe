<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use InvalidArgumentException;
use Shared\Enums\Ailment;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Interfaces\ValueObject;

/**
 * @phpstan-type CombatantArray array{pokemon_id: string|null, species_id: string, level: int, boost: int, is_shiny: bool, gender: string, types: list<string>, move_ids: list<string>, max_hp: int, current_hp: int, ailment: string|null, ailment_turns_left: int, confusion_turns_left: int, has_entered_field: bool, used_once_per_battle_move_ids: list<string>}
 */
final readonly class Combatant implements ValueObject
{
    /**
     * @param  list<PokemonType>  $types
     * @param  list<string>  $move_ids
     * @param  list<string>  $used_once_per_battle_move_ids
     */
    public function __construct(
        public ?string $pokemon_id,
        public string $species_id,
        public int $level,
        public int $boost,
        public bool $is_shiny,
        public PokemonGender $gender,
        public array $types,
        public array $move_ids,
        public int $max_hp,
        public int $current_hp,
        public ?Ailment $ailment,
        public int $ailment_turns_left,
        public int $confusion_turns_left,
        public bool $has_entered_field,
        public array $used_once_per_battle_move_ids,
    ) {
        $maxLevel       = (int) config('game.pokemon.max_level');
        $maxBoost       = (int) config('game.pokemon.max_boost');
        $maxActiveMoves = (int) config('game.pokemon.max_active_moves');

        throw_if($level < 1 || $level > $maxLevel, InvalidArgumentException::class, "The level must be between 1 and {$maxLevel}, [{$level}] given.");

        throw_if($boost < 0 || $boost > $maxBoost, InvalidArgumentException::class, "The boost must be between 0 and {$maxBoost}, [{$boost}] given.");

        throw_if(count($types) < 1 || count($types) > 2, InvalidArgumentException::class, 'A combatant must have one or two types.');

        throw_if(count($move_ids) < 1 || count($move_ids) > $maxActiveMoves, InvalidArgumentException::class, "A combatant must have between 1 and {$maxActiveMoves} moves.");

        throw_if($max_hp < 1, InvalidArgumentException::class, "The max hp must be at least 1, [{$max_hp}] given.");

        throw_if($current_hp < 0 || $current_hp > $max_hp, InvalidArgumentException::class, "The current hp must be between 0 and {$max_hp}, [{$current_hp}] given.");

        throw_if(in_array($ailment, [Ailment::CONFUSION, Ailment::UNKNOWN], true), InvalidArgumentException::class, 'Confusion is tracked by confusion_turns_left, and the unknown ailment is never inflicted.');

        throw_if($ailment_turns_left < 0 || $confusion_turns_left < 0, InvalidArgumentException::class, 'Turns left cannot be negative.');

        throw_if(array_diff($used_once_per_battle_move_ids, $move_ids) !== [], InvalidArgumentException::class, 'A used once-per-battle move must be one of the combatant moves.');
    }

    public function isFainted(): bool
    {
        return $this->current_hp === 0;
    }

    public function withCurrentHp(int $currentHp): self
    {
        return new self(...[...get_object_vars($this), 'current_hp' => $currentHp]);
    }

    public function withEnteredField(): self
    {
        return new self(...[...get_object_vars($this), 'has_entered_field' => true]);
    }

    public function withAilment(?Ailment $ailment, int $ailmentTurnsLeft): self
    {
        return new self(...[...get_object_vars($this), 'ailment' => $ailment, 'ailment_turns_left' => $ailmentTurnsLeft]);
    }

    public function withConfusionTurnsLeft(int $confusionTurnsLeft): self
    {
        return new self(...[...get_object_vars($this), 'confusion_turns_left' => $confusionTurnsLeft]);
    }

    public function withUsedOncePerBattleMove(string $moveId): self
    {
        return new self(...[...get_object_vars($this), 'used_once_per_battle_move_ids' => [...$this->used_once_per_battle_move_ids, $moveId]]);
    }

    /**
     * @param  CombatantArray  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            pokemon_id: $data['pokemon_id'],
            species_id: $data['species_id'],
            level: $data['level'],
            boost: $data['boost'],
            is_shiny: $data['is_shiny'],
            gender: PokemonGender::from($data['gender']),
            types: array_map(PokemonType::from(...), $data['types']),
            move_ids: $data['move_ids'],
            max_hp: $data['max_hp'],
            current_hp: $data['current_hp'],
            ailment: $data['ailment'] === null ? null : Ailment::from($data['ailment']),
            ailment_turns_left: $data['ailment_turns_left'],
            confusion_turns_left: $data['confusion_turns_left'],
            has_entered_field: $data['has_entered_field'],
            used_once_per_battle_move_ids: $data['used_once_per_battle_move_ids'],
        );
    }

    /**
     * @return CombatantArray
     */
    public function toArray(): array
    {
        return [
            'pokemon_id'                    => $this->pokemon_id,
            'species_id'                    => $this->species_id,
            'level'                         => $this->level,
            'boost'                         => $this->boost,
            'is_shiny'                      => $this->is_shiny,
            'gender'                        => $this->gender->value,
            'types'                         => array_map(fn (PokemonType $type) => $type->value, $this->types),
            'move_ids'                      => $this->move_ids,
            'max_hp'                        => $this->max_hp,
            'current_hp'                    => $this->current_hp,
            'ailment'                       => $this->ailment?->value,
            'ailment_turns_left'            => $this->ailment_turns_left,
            'confusion_turns_left'          => $this->confusion_turns_left,
            'has_entered_field'             => $this->has_entered_field,
            'used_once_per_battle_move_ids' => $this->used_once_per_battle_move_ids,
        ];
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->toArray() === $other->toArray();
    }
}
