<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Inventory\Actions\AddInventoryItemAction;
use App\Pokemon\Actions\CreatePokemonAction;
use App\Pokemon\ValueObjects\NewPokemon;
use Illuminate\Support\Facades\DB;
use Shared\Models\Pokeball;
use Shared\Models\Species;
use Shared\Models\User;

final class RegisterUserAction
{
    private User $user;

    public function handle(string $name, string $email, string $password, Species $starter): User
    {
        return DB::transaction(function () use ($name, $email, $password, $starter): User {
            $this->user = User::create([
                'name'     => $name,
                'email'    => $email,
                'password' => $password,
            ]);

            $this->giveStarter($starter);
            $this->giveStartingItems();

            return $this->user;
        });
    }

    private function giveStarter(Species $starter): void
    {
        new CreatePokemonAction()->handle(
            owner: $this->user,
            newPokemon: new NewPokemon(
                species: $starter,
                level: (int) config('game.registration.starter_level'),
                is_shiny: false,
                gender: $starter->getRandomGender(),
            ),
            pokeball: Pokeball::query()->where('slug', 'poke-ball')->firstOrFail(),
            joinPartyIfRoom: true,
        );
    }

    private function giveStartingItems(): void
    {
        /** @var array<string, int> $startingItems */
        $startingItems = config('game.registration.starting_items');

        foreach ($startingItems as $slug => $quantity) {
            new AddInventoryItemAction()->handle($this->user, Pokeball::query()->where('slug', $slug)->firstOrFail(), $quantity);
        }
    }
}
