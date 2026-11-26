<?php

declare(strict_types=1);

namespace Shared\Console\Commands;

use App\Battle\Actions\StartWildBattleAction;
use App\Pokemon\ValueObjects\NewPokemon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Shared\Base\DomainException;
use Shared\Enums\Area;
use Shared\Models\Species;
use Shared\Models\User;

#[Signature('battle:start-wild {email} {species-slug} {level} {--shiny}')]
#[Description('Start a wild battle for a user (local only, until exploring exists)')]
final class StartWildBattleCommand extends Command
{
    public function handle(): int
    {
        if (! app()->isLocal()) {
            $this->components->error('This command is only available in the local environment.');

            return self::FAILURE;
        }

        $user    = User::query()->where('email', $this->argument('email'))->firstOrFail();
        $species = Species::query()->where('slug', $this->argument('species-slug'))->firstOrFail();

        try {
            $battle = new StartWildBattleAction()->handle(
                user: $user,
                wildPokemon: new NewPokemon(
                    species: $species,
                    level: (int) $this->argument('level'),
                    isShiny: (bool) $this->option('shiny'),
                    gender: $species->getRandomGender(),
                ),
                area: Area::fromType($species->types->firstOrFail()),
            );
        } catch (DomainException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Battle [{$battle->id}] started against a wild {$species->getDisplayName((bool) $this->option('shiny'))}.");

        return self::SUCCESS;
    }
}
