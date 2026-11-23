<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Pokemon\Mechanics\StatsCalculator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\User;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

/**
 * @extends Factory<Battle>
 */
final class BattleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type'    => BattleType::WILD,
            'status'  => BattleStatus::ONGOING,
            'state'   => fn () => new BattleState(
                turn: 1,
                playerSide: new BattleSide(combatants: [$this->makeCombatant(pokemonId: fake()->uuid())], activeIndex: 0),
                opponentSide: new BattleSide(combatants: [$this->makeCombatant(pokemonId: null)], activeIndex: 0),
                isAwaitingSendOut: false,
                log: [],
            ),
        ];
    }

    private function makeCombatant(?string $pokemonId): Combatant
    {
        $level   = fake()->numberBetween(1, 100);
        $isShiny = fake()->boolean(10);
        $maxHp   = new StatsCalculator()->calculate($level, boost: 0, isShiny: $isShiny)->hp;

        return new Combatant(
            pokemonId: $pokemonId,
            speciesId: fake()->uuid(),
            level: $level,
            boost: 0,
            isShiny: $isShiny,
            gender: fake()->randomElement([PokemonGender::MALE, PokemonGender::FEMALE]),
            types: [fake()->randomElement(PokemonType::cases())],
            moveIds: [fake()->uuid(), fake()->uuid()],
            maxHp: $maxHp,
            currentHp: $maxHp,
            ailment: null,
            ailmentTurnsLeft: 0,
            confusionTurnsLeft: 0,
            hasEnteredField: $pokemonId !== null,
            usedOncePerBattleMoveIds: [],
        );
    }
}
