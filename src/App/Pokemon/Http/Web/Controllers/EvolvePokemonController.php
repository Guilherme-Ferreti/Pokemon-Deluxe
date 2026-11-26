<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Controllers;

use App\Pokemon\Actions\EvolvePokemonAction;
use App\Pokemon\Http\Web\Requests\EvolvePokemonRequest;
use Illuminate\Http\RedirectResponse;
use Shared\Base\Controller;
use Shared\Helpers\ToastHelper;
use Shared\Models\SpeciesEvolution;

final class EvolvePokemonController extends Controller
{
    public function __invoke(EvolvePokemonRequest $request, string $pokemon): RedirectResponse
    {
        $ownedPokemon = currentUser()->pokemon()->with('species')->findOrFail($pokemon);
        $evolution    = SpeciesEvolution::query()->whereKey($request->string('species_evolution_id'))->firstOrFail();
        $nameBefore   = $ownedPokemon->getDisplayName();

        $evolvedPokemon = new EvolvePokemonAction()->handle($ownedPokemon, $evolution);

        ToastHelper::success('Evolved!', "Your {$nameBefore} evolved into {$evolvedPokemon->load('species')->getDisplayName()}.");

        return back();
    }
}
