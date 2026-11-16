<?php

declare(strict_types=1);

namespace App\Battle\Http\Web\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SwitchPokemonRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'combatantIndex' => ['required', 'integer', 'min:0'],
        ];
    }
}
