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
                player_side: new BattleSide(combatants: [$this->makeCombatant(pokemonId: fake()->uuid())], active_index: 0),
                opponent_side: new BattleSide(combatants: [$this->makeCombatant(pokemonId: null)], active_index: 0),
                is_awaiting_send_out: false,
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
            pokemon_id: $pokemonId,
            species_id: fake()->uuid(),
            level: $level,
            boost: 0,
            is_shiny: $isShiny,
            gender: fake()->randomElement([PokemonGender::MALE, PokemonGender::FEMALE]),
            types: [fake()->randomElement(PokemonType::cases())],
            move_ids: [fake()->uuid(), fake()->uuid()],
            max_hp: $maxHp,
            current_hp: $maxHp,
            ailment: null,
            ailment_turns_left: 0,
            confusion_turns_left: 0,
            has_entered_field: $pokemonId !== null,
            used_once_per_battle_move_ids: [],
        );
    }
}
