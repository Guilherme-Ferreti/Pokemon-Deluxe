<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use InvalidArgumentException;
use Shared\Enums\Ailment;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Interfaces\ValueObject;

/**
 * @phpstan-type CombatantArray array{pokemon_id: string|null, species_id: string, level: int, boost: int, is_shiny: bool, gender: string, types: list<string>, move_ids: list<string>, max_hp: int, current_hp: int, ailment: string|null, ailment_turns_left: int, confusion_turns_left: int, has_entered_field: bool, used_once_per_battle_move_ids: list<string>, knocked_out_by: int|null}
 */
final readonly class Combatant implements ValueObject
{
    /**
     * @param  list<PokemonType>  $types
     * @param  list<string>  $moveIds
     * @param  list<string>  $usedOncePerBattleMoveIds
     */
    public function __construct(
        public ?string $pokemonId,
        public string $speciesId,
        public int $level,
        public int $boost,
        public bool $isShiny,
        public PokemonGender $gender,
        public array $types,
        public array $moveIds,
        public int $maxHp,
        public int $currentHp,
        public ?Ailment $ailment,
        public int $ailmentTurnsLeft,
        public int $confusionTurnsLeft,
        public bool $hasEnteredField,
        public array $usedOncePerBattleMoveIds,
        public ?int $knockedOutBy = null,
    ) {
        $maxLevel       = (int) config('game.pokemon.max_level');
        $maxBoost       = (int) config('game.pokemon.max_boost');
        $maxActiveMoves = (int) config('game.pokemon.max_active_moves');

        throw_if($level < 1 || $level > $maxLevel, InvalidArgumentException::class, "The level must be between 1 and {$maxLevel}, [{$level}] given.");

        throw_if($boost < 0 || $boost > $maxBoost, InvalidArgumentException::class, "The boost must be between 0 and {$maxBoost}, [{$boost}] given.");

        throw_if(count($types) < 1 || count($types) > 2, InvalidArgumentException::class, 'A combatant must have one or two types.');

        throw_if(count($moveIds) < 1 || count($moveIds) > $maxActiveMoves, InvalidArgumentException::class, "A combatant must have between 1 and {$maxActiveMoves} moves.");

        throw_if($maxHp < 1, InvalidArgumentException::class, "The max hp must be at least 1, [{$maxHp}] given.");

        throw_if($currentHp < 0 || $currentHp > $maxHp, InvalidArgumentException::class, "The current hp must be between 0 and {$maxHp}, [{$currentHp}] given.");

        throw_if(in_array($ailment, [Ailment::CONFUSION, Ailment::UNKNOWN], true), InvalidArgumentException::class, 'Confusion is tracked by confusion_turns_left, and the unknown ailment is never inflicted.');

        throw_if($ailmentTurnsLeft < 0 || $confusionTurnsLeft < 0, InvalidArgumentException::class, 'Turns left cannot be negative.');

        throw_if(array_diff($usedOncePerBattleMoveIds, $moveIds) !== [], InvalidArgumentException::class, 'A used once-per-battle move must be one of the combatant moves.');

        throw_if($knockedOutBy !== null && $knockedOutBy < 0, InvalidArgumentException::class, "The knocked out by index cannot be negative, [{$knockedOutBy}] given.");
    }

    public function isFainted(): bool
    {
        return $this->currentHp === 0;
    }

    public function withCurrentHp(int $currentHp): self
    {
        return new self(...[...get_object_vars($this), 'currentHp' => $currentHp]);
    }

    public function withEnteredField(): self
    {
        return new self(...[...get_object_vars($this), 'hasEnteredField' => true]);
    }

    public function withAilment(?Ailment $ailment, int $ailmentTurnsLeft): self
    {
        return new self(...[...get_object_vars($this), 'ailment' => $ailment, 'ailmentTurnsLeft' => $ailmentTurnsLeft]);
    }

    public function withConfusionTurnsLeft(int $confusionTurnsLeft): self
    {
        return new self(...[...get_object_vars($this), 'confusionTurnsLeft' => $confusionTurnsLeft]);
    }

    public function withUsedOncePerBattleMove(string $moveId): self
    {
        return new self(...[...get_object_vars($this), 'usedOncePerBattleMoveIds' => [...$this->usedOncePerBattleMoveIds, $moveId]]);
    }

    public function withKnockedOutBy(int $combatantIndex): self
    {
        return new self(...[...get_object_vars($this), 'knockedOutBy' => $combatantIndex]);
    }

    /**
     * @param  CombatantArray  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            pokemonId: $data['pokemon_id'],
            speciesId: $data['species_id'],
            level: $data['level'],
            boost: $data['boost'],
            isShiny: $data['is_shiny'],
            gender: PokemonGender::from($data['gender']),
            types: array_map(PokemonType::from(...), $data['types']),
            moveIds: $data['move_ids'],
            maxHp: $data['max_hp'],
            currentHp: $data['current_hp'],
            ailment: $data['ailment'] === null ? null : Ailment::from($data['ailment']),
            ailmentTurnsLeft: $data['ailment_turns_left'],
            confusionTurnsLeft: $data['confusion_turns_left'],
            hasEnteredField: $data['has_entered_field'],
            usedOncePerBattleMoveIds: $data['used_once_per_battle_move_ids'],
            knockedOutBy: $data['knocked_out_by'],
        );
    }

    /**
     * @return CombatantArray
     */
    public function toArray(): array
    {
        return [
            'pokemon_id'                    => $this->pokemonId,
            'species_id'                    => $this->speciesId,
            'level'                         => $this->level,
            'boost'                         => $this->boost,
            'is_shiny'                      => $this->isShiny,
            'gender'                        => $this->gender->value,
            'types'                         => array_map(fn (PokemonType $type) => $type->value, $this->types),
            'move_ids'                      => $this->moveIds,
            'max_hp'                        => $this->maxHp,
            'current_hp'                    => $this->currentHp,
            'ailment'                       => $this->ailment?->value,
            'ailment_turns_left'            => $this->ailmentTurnsLeft,
            'confusion_turns_left'          => $this->confusionTurnsLeft,
            'has_entered_field'             => $this->hasEnteredField,
            'used_once_per_battle_move_ids' => $this->usedOncePerBattleMoveIds,
            'knocked_out_by'                => $this->knockedOutBy,
        ];
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->toArray() === $other->toArray();
    }
}
