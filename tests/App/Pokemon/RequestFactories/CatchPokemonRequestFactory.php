<?php

declare(strict_types=1);

namespace Tests\App\Pokemon\RequestFactories;

use App\Pokemon\Http\Web\Requests\Incubator\CatchPokemonRequest;
use Illuminate\Support\Str;
use Shared\Models\Pokeball;
use Worksome\RequestFactories\RequestFactory;

final class CatchPokemonRequestFactory extends RequestFactory
{
    public static string $factory = CatchPokemonRequest::class;

    public function definition(): array
    {
        return [
            'pokeballId' => fn (): string => Pokeball::factory()->create()->id,
        ];
    }

    public function withInvalidPokeball(): static
    {
        return $this->state([
            'pokeballId' => Str::uuid()->toString(),
        ]);
    }
}
