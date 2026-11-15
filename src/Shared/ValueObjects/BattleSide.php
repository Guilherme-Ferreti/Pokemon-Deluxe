<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use InvalidArgumentException;
use Shared\Interfaces\ValueObject;

/**
 * @phpstan-import-type CombatantArray from Combatant
 *
 * @phpstan-type BattleSideArray array{combatants: list<CombatantArray>, active_index: int}
 */
final readonly class BattleSide implements ValueObject
{
    /**
     * @param  list<Combatant>  $combatants
     */
    public function __construct(
        public array $combatants,
        public int $active_index,
    ) {
        throw_if($combatants === [], InvalidArgumentException::class, 'A battle side must have at least one combatant.');

        throw_if(! array_key_exists($active_index, $combatants), InvalidArgumentException::class, sprintf('The active index must be between 0 and %d, [%d] given.', count($combatants) - 1, $active_index));
    }

    public function activeCombatant(): Combatant
    {
        return $this->combatants[$this->active_index];
    }

    public function hasCombatantsLeft(): bool
    {
        return array_any($this->combatants, fn (Combatant $combatant) => ! $combatant->isFainted());
    }

    public function withActiveCombatant(Combatant $combatant): self
    {
        $combatants = array_map(
            fn (Combatant $current, int $index) => $index === $this->active_index ? $combatant : $current,
            $this->combatants,
            array_keys($this->combatants),
        );

        return new self(combatants: $combatants, active_index: $this->active_index);
    }

    /**
     * @param  BattleSideArray  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            combatants: array_map(Combatant::fromArray(...), $data['combatants']),
            active_index: $data['active_index'],
        );
    }

    /**
     * @return BattleSideArray
     */
    public function toArray(): array
    {
        return [
            'combatants'   => array_map(fn (Combatant $combatant) => $combatant->toArray(), $this->combatants),
            'active_index' => $this->active_index,
        ];
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->toArray() === $other->toArray();
    }
}
