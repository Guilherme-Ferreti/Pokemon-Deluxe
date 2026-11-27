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
            'maxStones'   => ['required', 'integer', 'min:1'],
            'targetBoost' => ['nullable', 'integer', 'min:1', 'max:' . (int) config('game.pokemon.max_boost')],
        ];
    }
}
