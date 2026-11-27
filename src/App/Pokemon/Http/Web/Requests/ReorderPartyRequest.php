<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReorderPartyRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pokemonIds'   => ['required', 'array', 'min:1', 'max:' . (int) config('game.user.max_party_size')],
            'pokemonIds.*' => ['required', 'uuid', 'distinct'],
        ];
    }
}
