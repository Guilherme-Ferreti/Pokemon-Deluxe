<?php

declare(strict_types=1);

namespace App\Battle\Actions;

use App\Battle\Events\BattleEnded;
use App\Battle\Mechanics\EarnedExperienceCalculator;
use App\Pokemon\Actions\AwardExperienceAction;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Models\Battle;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\ValueObjects\BattleLogEntry;
use Shared\ValueObjects\Combatant;
use Shared\ValueObjects\ExperienceGain;

final class EndBattleAction
{
    private Battle $battle;

    private BattleStatus $status;

    /** @var EloquentCollection<string, Pokemon> */
    private EloquentCollection $playerPokemon;

    public function handle(Battle $battle, BattleStatus $status): Battle
    {
        $this->battle = $battle;
        $this->status = $status;

        $this->ensureStatusIsAnEnding();
        $this->ensureBattleIsOngoing();

        DB::transaction(fn () => $this->endBattle());

        BattleEnded::dispatch(
            $this->battle->id,
            $this->battle->user_id,
            $this->battle->type,
            $this->status,
            in_array($this->status, [BattleStatus::WON, BattleStatus::CAUGHT], true),
        );

        return $this->battle;
    }

    private function ensureStatusIsAnEnding(): void
    {
        throw_if($this->status === BattleStatus::ONGOING, InvalidArgumentException::class, 'A battle cannot end as ongoing.');
    }

    private function ensureBattleIsOngoing(): void
    {
        throw_if($this->battle->status !== BattleStatus::ONGOING, LogicException::class, 'The battle has already ended.');
    }

    private function endBattle(): void
    {
        $this->loadPlayerPokemon();

        $gains = $this->payExperience();

        $this->battle->update([
            'status' => $this->status,
            'state'  => $this->battle->state
                ->withExperienceGains($gains)
                ->withLogEntries($this->experienceLogEntries($gains)),
        ]);
    }

    /**
     * @param  list<ExperienceGain>  $gains
     * @return list<BattleLogEntry>
     */
    private function experienceLogEntries(array $gains): array
    {
        if ($this->battle->type === BattleType::BATTLE_TOWER) {
            return [];
        }

        $combatants = $this->battle->state->player_side->combatants;
        $species    = Species::query()
            ->whereKey(array_map(fn (Combatant $combatant) => $combatant->species_id, $combatants))
            ->get()
            ->keyBy('id');

        $turn    = $this->battle->state->lastPlayedTurn();
        $entries = [];

        foreach ($gains as $gain) {
            if ($gain->earned_experience === 0) {
                continue;
            }

            $combatant = $combatants[$gain->combatant_index];
            $name      = $species->findOrFail($combatant->species_id)->getDisplayName($combatant->is_shiny);

            $entries[] = new BattleLogEntry($turn, BattleLogKind::EVENT, sprintf('%s gained %s XP!', $name, number_format($gain->earned_experience)));

            if ($gain->level_after > $gain->level_before) {
                $entries[] = new BattleLogEntry($turn, BattleLogKind::EVENT, "{$name} grew to Lv {$gain->level_after}!");
            }
        }

        return $entries;
    }

    private function loadPlayerPokemon(): void
    {
        $pokemonIds = array_filter(array_map(
            fn (Combatant $combatant) => $combatant->pokemon_id,
            $this->battle->state->player_side->combatants,
        ));

        $this->playerPokemon = Pokemon::query()->whereKey($pokemonIds)->get()->keyBy('id');
    }

    /**
     * @return list<ExperienceGain>
     */
    private function payExperience(): array
    {
        $earnedByCombatant = new EarnedExperienceCalculator()->earnedByCombatant(
            $this->battle->state,
            $this->battle->type,
            $this->status,
        );

        $gains = [];

        foreach ($earnedByCombatant as $combatantIndex => $earned) {
            $gains[] = $this->payCombatant($combatantIndex, $earned);
        }

        return $gains;
    }

    private function payCombatant(int $combatantIndex, int $earned): ExperienceGain
    {
        $combatant = $this->battle->state->player_side->combatants[$combatantIndex];
        $pokemon   = $combatant->pokemon_id === null ? null : $this->playerPokemon->get($combatant->pokemon_id);

        if ($this->battle->type === BattleType::BATTLE_TOWER) {
            return new ExperienceGain($combatantIndex, $earned, $combatant->level, $combatant->level);
        }

        if (! $pokemon instanceof Pokemon) {
            return new ExperienceGain($combatantIndex, 0, $combatant->level, $combatant->level);
        }

        $experienceBefore = $pokemon->experience;
        $levelBefore      = $pokemon->level;

        $pokemon = new AwardExperienceAction()->handle($pokemon, $earned);

        return new ExperienceGain($combatantIndex, $pokemon->experience - $experienceBefore, $levelBefore, $pokemon->level);
    }
}
