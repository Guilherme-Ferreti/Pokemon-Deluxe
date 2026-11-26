<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Economy\Actions\AddMoneyAction;
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
            $this->giveStartingMoney();

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
                isShiny: false,
                gender: $starter->getRandomGender(),
            ),
            pokeball: Pokeball::query()->where('slug', 'poke-ball')->firstOrFail(),
            joinPartyIfRoom: true,
        );
    }

    private function giveStartingItems(): void
    {
        /** @var array<string, int> $startingPokeballs */
        $startingPokeballs = config('game.registration.starting_items.pokeballs');

        Pokeball::query()
            ->whereIn('slug', array_keys($startingPokeballs))
            ->get()
            ->each(fn (Pokeball $pokeball) => new AddInventoryItemAction()->handle(
                user: $this->user,
                item: $pokeball,
                quantity: $startingPokeballs[$pokeball->slug],
            ));
    }

    private function giveStartingMoney(): void
    {
        new AddMoneyAction()->handle($this->user, (int) config('game.economy.starting_money'));
    }
}
