<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use InvalidArgumentException;
use Shared\Enums\Area;
use Shared\Interfaces\ValueObject;

/**
 * @phpstan-import-type BattleLogEntryArray from BattleLogEntry
 * @phpstan-import-type BattleSideArray from BattleSide
 * @phpstan-import-type ExperienceGainArray from ExperienceGain
 *
 * @phpstan-type BattleStateArray array{turn: int, player_side: BattleSideArray, opponent_side: BattleSideArray, is_awaiting_send_out: bool, log: list<BattleLogEntryArray>, experience_gains: list<ExperienceGainArray>, area?: string|null, caught_pokemon_id?: string|null}
 */
final readonly class BattleState implements ValueObject
{
    /**
     * @param  list<BattleLogEntry>  $log
     * @param  list<ExperienceGain>  $experienceGains
     */
    public function __construct(
        public int $turn,
        public BattleSide $playerSide,
        public BattleSide $opponentSide,
        public bool $isAwaitingSendOut,
        public array $log,
        public array $experienceGains = [],
        public ?Area $area = null,
        public ?string $caughtPokemonId = null,
    ) {
        throw_if($turn < 1, InvalidArgumentException::class, "The turn must be at least 1, [{$turn}] given.");
    }

    public function withPlayerSide(BattleSide $playerSide): self
    {
        return new self(...[...get_object_vars($this), 'playerSide' => $playerSide]);
    }

    public function withOpponentSide(BattleSide $opponentSide): self
    {
        return new self(...[...get_object_vars($this), 'opponentSide' => $opponentSide]);
    }

    public function withAwaitingSendOut(bool $isAwaitingSendOut): self
    {
        return new self(...[...get_object_vars($this), 'isAwaitingSendOut' => $isAwaitingSendOut]);
    }

    public function lastPlayedTurn(): int
    {
        return $this->turn - 1;
    }

    public function endTurn(): int
    {
        return $this->turn;
    }

    /**
     * @param  list<BattleLogEntry>  $logEntries
     */
    public function withLogEntries(array $logEntries): self
    {
        return new self(...[...get_object_vars($this), 'log' => [...$this->log, ...$logEntries]]);
    }

    /**
     * @param  list<BattleLogEntry>  $logEntries
     */
    public function withNextTurn(array $logEntries): self
    {
        return new self(...[...get_object_vars($this), 'turn' => $this->turn + 1, 'log' => [...$this->log, ...$logEntries]]);
    }

    /**
     * @param  list<ExperienceGain>  $experienceGains
     */
    public function withExperienceGains(array $experienceGains): self
    {
        return new self(...[...get_object_vars($this), 'experienceGains' => $experienceGains]);
    }

    public function withCaughtPokemonId(string $caughtPokemonId): self
    {
        return new self(...[...get_object_vars($this), 'caughtPokemonId' => $caughtPokemonId]);
    }

    /**
     * @param  BattleStateArray  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            turn: $data['turn'],
            playerSide: BattleSide::fromArray($data['player_side']),
            opponentSide: BattleSide::fromArray($data['opponent_side']),
            isAwaitingSendOut: $data['is_awaiting_send_out'],
            log: array_map(BattleLogEntry::fromArray(...), $data['log']),
            experienceGains: array_map(ExperienceGain::fromArray(...), $data['experience_gains']),
            area: isset($data['area']) ? Area::from($data['area']) : null,
            caughtPokemonId: $data['caught_pokemon_id'] ?? null,
        );
    }

    /**
     * @return BattleStateArray
     */
    public function toArray(): array
    {
        return [
            'turn'                 => $this->turn,
            'player_side'          => $this->playerSide->toArray(),
            'opponent_side'        => $this->opponentSide->toArray(),
            'is_awaiting_send_out' => $this->isAwaitingSendOut,
            'log'                  => array_map(fn (BattleLogEntry $entry) => $entry->toArray(), $this->log),
            'experience_gains'     => array_map(fn (ExperienceGain $gain) => $gain->toArray(), $this->experienceGains),
            'area'                 => $this->area?->value,
            'caught_pokemon_id'    => $this->caughtPokemonId,
        ];
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->toArray() === $other->toArray();
    }
}
