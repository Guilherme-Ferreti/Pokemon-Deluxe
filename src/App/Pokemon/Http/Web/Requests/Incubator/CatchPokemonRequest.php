<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Requests\Incubator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shared\Models\Pokeball;

final class CatchPokemonRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pokeballId' => ['required', 'string', Rule::exists(Pokeball::class, 'id')],
        ];
    }
}
