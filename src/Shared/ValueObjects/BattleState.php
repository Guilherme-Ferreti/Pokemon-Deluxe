<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use InvalidArgumentException;
use Shared\Interfaces\ValueObject;

/**
 * @phpstan-import-type BattleSideArray from BattleSide
 *
 * @phpstan-type BattleStateArray array{turn: int, player_side: BattleSideArray, opponent_side: BattleSideArray, is_awaiting_send_out: bool, log: list<string>}
 */
final readonly class BattleState implements ValueObject
{
    /**
     * @param  list<string>  $log
     */
    public function __construct(
        public int $turn,
        public BattleSide $player_side,
        public BattleSide $opponent_side,
        public bool $is_awaiting_send_out,
        public array $log,
    ) {
        throw_if($turn < 1, InvalidArgumentException::class, "The turn must be at least 1, [{$turn}] given.");
    }

    public function withPlayerSide(BattleSide $playerSide): self
    {
        return new self(...[...get_object_vars($this), 'player_side' => $playerSide]);
    }

    public function withOpponentSide(BattleSide $opponentSide): self
    {
        return new self(...[...get_object_vars($this), 'opponent_side' => $opponentSide]);
    }

    public function withAwaitingSendOut(bool $isAwaitingSendOut): self
    {
        return new self(...[...get_object_vars($this), 'is_awaiting_send_out' => $isAwaitingSendOut]);
    }

    /**
     * @param  list<string>  $logLines
     */
    public function withLogLines(array $logLines): self
    {
        return new self(...[...get_object_vars($this), 'log' => [...$this->log, ...$logLines]]);
    }

    /**
     * @param  list<string>  $logLines
     */
    public function withNextTurn(array $logLines): self
    {
        return new self(...[...get_object_vars($this), 'turn' => $this->turn + 1, 'log' => [...$this->log, ...$logLines]]);
    }

    /**
     * @param  BattleStateArray  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            turn: $data['turn'],
            player_side: BattleSide::fromArray($data['player_side']),
            opponent_side: BattleSide::fromArray($data['opponent_side']),
            is_awaiting_send_out: $data['is_awaiting_send_out'],
            log: $data['log'],
        );
    }

    /**
     * @return BattleStateArray
     */
    public function toArray(): array
    {
        return [
            'turn'                 => $this->turn,
            'player_side'          => $this->player_side->toArray(),
            'opponent_side'        => $this->opponent_side->toArray(),
            'is_awaiting_send_out' => $this->is_awaiting_send_out,
            'log'                  => $this->log,
        ];
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->toArray() === $other->toArray();
    }
}
