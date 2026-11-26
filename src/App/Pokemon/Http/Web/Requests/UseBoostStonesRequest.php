<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UseBoostStonesRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'max_stones'   => ['required', 'integer', 'min:1'],
            'target_boost' => ['nullable', 'integer', 'min:1', 'max:' . (int) config('game.pokemon.max_boost')],
        ];
    }
}
