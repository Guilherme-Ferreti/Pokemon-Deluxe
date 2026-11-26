<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shared\Models\SpeciesEvolution;

final class EvolvePokemonRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'species_evolution_id' => ['required', 'string', Rule::exists(SpeciesEvolution::class, 'id')],
        ];
    }
}
