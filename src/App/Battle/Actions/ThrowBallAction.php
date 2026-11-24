<?php

declare(strict_types=1);

namespace App\Battle\Actions;

use App\Battle\Data\TurnData;
use App\Battle\Enums\Side;
use App\Battle\Exceptions\BattleNotReadyForTurnException;
use App\Battle\Exceptions\NotAWildBattleException;
use App\Battle\Mechanics\BallMultiplierCalculator;
use App\Battle\Mechanics\CatchChanceCalculator;
use App\Battle\Pipes\Turn\ApplyEndOfTurnDamage;
use App\Battle\Pipes\Turn\ChooseOpponentMove;
use App\Battle\Pipes\Turn\CreditOpponentKnockout;
use App\Battle\Pipes\Turn\LoadMovesAndSpecies;
use App\Battle\Pipes\Turn\SaveTurn;
use App\Battle\Pipes\Turn\TakeActions;
use App\Battle\Support\CatchContextBuilder;
use App\Battle\Support\LockKeys;
use App\Battle\ValueObjects\CatchContext;
use App\Inventory\Actions\RemoveInventoryItemAction;
use App\Pokemon\Actions\CreatePokemonAction;
use App\Pokemon\Events\PokemonCaught;
use App\Pokemon\Exceptions\BoxFullException;
use App\Pokemon\ValueObjects\NewPokemon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Pipeline;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Helpers\RandomHelper;
use Shared\Models\Battle;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\User;
use Shared\ValueObjects\BattleLogEntry;
use Shared\ValueObjects\Combatant;

final class ThrowBallAction
{
    private Battle $battle;

    private Pokeball $pokeball;

    private User $user;

    private CatchContext $catchContext;

    private ?Pokemon $caughtPokemon = null;

    public function handle(Battle $battle, Pokeball $pokeball): Battle
    {
        $this->battle   = $battle;
        $this->pokeball = $pokeball;

        Cache::withoutOverlapping(
            key: LockKeys::battle($battle->id),
            callback: fn () => $this->throwBall(),
        );

        if ($this->caughtPokemon instanceof Pokemon) {
            PokemonCaught::dispatch($this->caughtPokemon->id, $this->user->id);
        }

        return $this->battle;
    }

    private function throwBall(): void
    {
        $this->battle->refresh();
        $this->user = $this->battle->user;

        $this->ensureBattleIsWild();
        $this->ensureBattleIsReadyForTurn();
        $this->ensureBoxIsNotFull();

        $this->loadCatchContext();

        DB::transaction(function (): void {
            new RemoveInventoryItemAction()->handle($this->user, $this->pokeball);

            if ($this->rollIsCaught()) {
                $this->catchWildPokemon();

                return;
            }

            $this->letOpponentActAfterBreakingFree();
        });
    }

    private function ensureBattleIsWild(): void
    {
        throw_if($this->battle->type !== BattleType::WILD, NotAWildBattleException::class);
    }

    private function ensureBattleIsReadyForTurn(): void
    {
        $isReady = $this->battle->status === BattleStatus::ONGOING && ! $this->battle->state->isAwaitingSendOut;

        throw_unless($isReady, BattleNotReadyForTurnException::class);
    }

    private function ensureBoxIsNotFull(): void
    {
        $pokemonCount = $this->user->pokemon()->count();

        throw_if($pokemonCount >= (int) config('game.user.max_box_size'), BoxFullException::class);
    }

    private function loadCatchContext(): void
    {
        $this->catchContext = new CatchContextBuilder()->build($this->battle);
    }

    private function wildCombatant(): Combatant
    {
        return $this->battle->state->opponentSide->activeCombatant();
    }

    private function rollIsCaught(): bool
    {
        $ballMultiplierCalculator = new BallMultiplierCalculator;

        if ($ballMultiplierCalculator->isGuaranteed($this->pokeball)) {
            return true;
        }

        $wildCombatant = $this->wildCombatant();

        $catchChance = new CatchChanceCalculator()->calculate(
            $this->catchContext->wildSpecies,
            $wildCombatant->currentHp,
            $wildCombatant->maxHp,
            $wildCombatant->ailment,
            $ballMultiplierCalculator->multiplier($this->pokeball, $this->catchContext),
        );

        return RandomHelper::chance($catchChance);
    }

    private function catchWildPokemon(): void
    {
        $wildCombatant = $this->wildCombatant();
        $wildSpecies   = $this->catchContext->wildSpecies;

        $this->caughtPokemon = new CreatePokemonAction()->handle(
            owner: $this->user,
            newPokemon: new NewPokemon($wildSpecies, $wildCombatant->level, $wildCombatant->isShiny, $wildCombatant->gender),
            pokeball: $this->pokeball,
            joinPartyIfRoom: true,
        );

        $turn = $this->battle->state->turn;

        $this->battle->update([
            'state' => $this->battle->state
                ->withLogEntries([
                    new BattleLogEntry($turn, BattleLogKind::EVENT, $this->thrownBallText()),
                    new BattleLogEntry($turn, BattleLogKind::RESULT, "Gotcha! {$wildSpecies->getDisplayName($wildCombatant->isShiny)} was caught!"),
                ])
                ->withCaughtPokemonId($this->caughtPokemon->id),
        ]);

        new EndBattleAction()->handle($this->battle, BattleStatus::CAUGHT);
    }

    private function letOpponentActAfterBreakingFree(): void
    {
        $turn = new TurnData($this->battle);

        $turn->log(BattleLogKind::EVENT, $this->thrownBallText());
        $turn->log(BattleLogKind::EVENT, 'Oh no! The Pokémon broke free!');

        $turn->turnOrder = [Side::PLAYER, Side::OPPONENT];

        Pipeline::send($turn)
            ->through([
                LoadMovesAndSpecies::class,
                ChooseOpponentMove::class,
                TakeActions::class,
                ApplyEndOfTurnDamage::class,
                CreditOpponentKnockout::class,
                SaveTurn::class,
            ])
            ->thenReturn();
    }

    private function thrownBallText(): string
    {
        $article = preg_match('/^[aeiou]/i', $this->pokeball->name) === 1 ? 'an' : 'a';

        return "You threw {$article} {$this->pokeball->name}!";
    }
}
