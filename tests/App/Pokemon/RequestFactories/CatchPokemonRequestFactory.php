<?php

declare(strict_types=1);

namespace Tests\App\Pokemon\RequestFactories;

use App\Pokemon\Http\Web\Requests\Incubator\CatchPokemonRequest;
use Closure;
use Illuminate\Support\Str;
use Shared\Models\Pokeball;
use Worksome\RequestFactories\RequestFactory;

final class CatchPokemonRequestFactory extends RequestFactory
{
    public static string $factory = CatchPokemonRequest::class;

    /**
     * @return Closure[]
     */
    public function definition(): array
    {
        return [
            'pokeballId' => fn () => Pokeball::factory()->create()->id,
        ];
    }

    public function withInvalidPokeball(): static
    {
        return $this->state([
            'pokeballId' => Str::uuid()->toString(),
        ]);
    }
}
