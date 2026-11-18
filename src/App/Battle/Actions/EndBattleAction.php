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
                ->withLogEntries([...$this->resultLogEntries(), ...$this->experienceLogEntries($gains)]),
        ]);
    }

    /**
     * @return list<BattleLogEntry>
     */
    private function resultLogEntries(): array
    {
        $text = match ($this->status) {
            BattleStatus::WON  => 'You won!',
            BattleStatus::LOST => 'You lost...',
            default            => null,
        };

        return $text === null ? [] : [new BattleLogEntry($this->battle->state->endTurn(), BattleLogKind::RESULT, $text)];
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

        $turn    = $this->battle->state->endTurn();
        $entries = [];

        foreach ($gains as $gain) {
            if ($gain->earned_experience === 0) {
                continue;
            }

            $combatant = $combatants[$gain->combatant_index];
            $name      = $species->findOrFail($combatant->species_id)->getDisplayName($combatant->is_shiny);

            $experience = number_format($gain->earned_experience);

            $entries[] = new BattleLogEntry($turn, BattleLogKind::EVENT, $gain->level_after > $gain->level_before
                ? "{$name} gained {$experience} XP and grew to level {$gain->level_after}!"
                : "{$name} gained {$experience} XP!");
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
            return new ExperienceGain($combatantIndex, $earned, $combatant->level, $combatant->level, is_from_experience_share: false);
        }

        if (! $pokemon instanceof Pokemon) {
            return new ExperienceGain($combatantIndex, 0, $combatant->level, $combatant->level, is_from_experience_share: false);
        }

        $experienceBefore = $pokemon->experience;
        $levelBefore      = $pokemon->level;

        $pokemon = new AwardExperienceAction()->handle($pokemon, $earned);

        return new ExperienceGain($combatantIndex, $pokemon->experience - $experienceBefore, $levelBefore, $pokemon->level, is_from_experience_share: false);
    }
}
