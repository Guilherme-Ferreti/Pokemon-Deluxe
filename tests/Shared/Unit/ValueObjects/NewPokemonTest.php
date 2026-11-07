<?php

declare(strict_types=1);

use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonGenderRate;
use Shared\Models\Incubation;
use Shared\Models\Species;
use Shared\ValueObjects\IncubatorIndex;
use Shared\ValueObjects\NewPokemon;

describe('level', function () {
    it('accepts a level within the allowed range', function () {
        $species = Species::factory()->create(['gender_rate' => PokemonGenderRate::ONLY_MALE]);
        $max     = (int) config('game.pokemon.max_level');

        expect(fn () => new NewPokemon($species, 1, false, PokemonGender::MALE))
            ->not->toThrow(InvalidArgumentException::class)
            ->and(fn () => new NewPokemon($species, $max, false, PokemonGender::MALE))->not->toThrow(InvalidArgumentException::class);
    });

    it('rejects a level outside the allowed range', function (int $level) {
        $species = Species::factory()->create();

        expect(fn () => new NewPokemon($species, $level, false, PokemonGender::MALE))->toThrow(InvalidArgumentException::class);
    })->with([
        'below one' => fn () => 0,
        'negative'  => fn () => -1,
        'above max' => fn () => (int) config('game.pokemon.max_level') + 1,
    ]);
});

describe('gender', function () {
    it('accepts a genderless species when the gender is genderless', function () {
        $species = Species::factory()->create(['gender_rate' => PokemonGenderRate::GENDERLESS]);

        expect(fn () => new NewPokemon($species, 50, false, PokemonGender::GENDERLESS))
            ->not->toThrow(InvalidArgumentException::class);
    });

    it('rejects a gendered pokemon for a genderless species', function (PokemonGender $gender) {
        $species = Species::factory()->create(['gender_rate' => PokemonGenderRate::GENDERLESS]);

        expect(fn () => new NewPokemon($species, 50, false, $gender))
            ->toThrow(InvalidArgumentException::class);
    })->with([
        'male'   => PokemonGender::MALE,
        'female' => PokemonGender::FEMALE,
    ]);

    it('accepts male and female genders for a gendered species', function (PokemonGender $gender) {
        $species = $species = Species::factory()->create(['gender_rate' => PokemonGenderRate::EQUAL]);

        expect(fn () => new NewPokemon($species, 50, false, $gender))
            ->not->toThrow(InvalidArgumentException::class);
    })->with([
        'male'   => PokemonGender::MALE,
        'female' => PokemonGender::FEMALE,
    ]);

    it('rejects a genderless gender for a gendered species', function () {
        $species = Species::factory()->create(['gender_rate' => PokemonGenderRate::EQUAL]);

        expect(fn () => new NewPokemon($species, 50, false, PokemonGender::GENDERLESS))
            ->toThrow(InvalidArgumentException::class);
    });

    it('accepts male gender for a male only species', function () {
        $species = Species::factory()->create(['gender_rate' => PokemonGenderRate::ONLY_MALE]);

        expect(fn () => new NewPokemon($species, 50, false, PokemonGender::MALE))
            ->not->toThrow(InvalidArgumentException::class);
    });

    it('rejects other genders for a male only species', function (PokemonGender $gender) {
        $species = Species::factory()->create(['gender_rate' => PokemonGenderRate::ONLY_MALE]);

        expect(fn () => new NewPokemon($species, 50, false, $gender))
            ->toThrow(InvalidArgumentException::class);
    })->with([
        'female'     => PokemonGender::FEMALE,
        'genderless' => PokemonGender::GENDERLESS,
    ]);

    it('accepts female gender for a female only species', function () {
        $species = Species::factory()->create(['gender_rate' => PokemonGenderRate::ONLY_FEMALE]);

        expect(fn () => new NewPokemon($species, 50, false, PokemonGender::FEMALE))
            ->not->toThrow(InvalidArgumentException::class);
    });

    it('rejects other genders for a female only species', function (PokemonGender $gender) {
        $species = Species::factory()->create(['gender_rate' => PokemonGenderRate::ONLY_FEMALE]);

        expect(fn () => new NewPokemon($species, 50, false, $gender))
            ->toThrow(InvalidArgumentException::class);
    })->with([
        'male'       => PokemonGender::MALE,
        'genderless' => PokemonGender::GENDERLESS,
    ]);
});

describe('equality', function () {
    it('equals another pokemon with the same values', function () {
        $species = Species::factory()->create();
        $gender  = $species->getRandomGender();

        $first  = new NewPokemon($species, 50, true, $gender);
        $second = new NewPokemon($species, 50, true, $gender);

        expect($first->equals($second))->toBeTrue();
    });

    it('does not equal a pokemon with a different species', function () {
        $species      = Species::factory()->create(['gender_rate' => PokemonGenderRate::GENDERLESS]);
        $otherSpecies = Species::factory()->create(['gender_rate' => PokemonGenderRate::GENDERLESS]);

        $first  = new NewPokemon($species, 50, true, PokemonGender::GENDERLESS);
        $second = new NewPokemon($otherSpecies, 50, true, PokemonGender::GENDERLESS);

        expect($first->equals($second))->toBeFalse();
    });

    it('does not equal a pokemon with a different level', function () {
        $species = Species::factory()->create(['gender_rate' => PokemonGenderRate::GENDERLESS]);

        $first  = new NewPokemon($species, 50, true, PokemonGender::GENDERLESS);
        $second = new NewPokemon($species, 51, true, PokemonGender::GENDERLESS);

        expect($first->equals($second))->toBeFalse();
    });

    it('does not equal a pokemon with a different shiny status', function () {
        $species = Species::factory()->create(['gender_rate' => PokemonGenderRate::GENDERLESS]);

        $first  = new NewPokemon($species, 50, true, PokemonGender::GENDERLESS);
        $second = new NewPokemon($species, 50, false, PokemonGender::GENDERLESS);

        expect($first->equals($second))->toBeFalse();
    });

    it('does not equal a pokemon with a different gender', function () {
        $species = Species::factory()->create(['gender_rate' => PokemonGenderRate::EQUAL]);

        $first  = new NewPokemon($species, 50, true, PokemonGender::MALE);
        $second = new NewPokemon($species, 50, true, PokemonGender::FEMALE);

        expect($first->equals($second))->toBeFalse();
    });

    it('does not equal a different value object', function () {
        $species = Species::factory()->make();

        $first  = new NewPokemon($species, 50, true, $species->getRandomGender());
        $second = new IncubatorIndex(1);

        expect($first->equals($second))->toBeFalse();
    });
});

describe('from incubation', function () {
    it('creates a new instance from an incubation', function () {
        $species = Species::factory()->create();

        $incubation = Incubation::factory()->for($species)->create();

        $pokemon = NewPokemon::fromIncubation($incubation);

        expect($pokemon->species->is($species))->toBeTrue()
            ->and($pokemon->level)->toBe(1)
            ->and($pokemon->is_shiny)->toBe($incubation->is_shiny)
            ->and($pokemon->gender)->toBe($incubation->gender);
    });
});
