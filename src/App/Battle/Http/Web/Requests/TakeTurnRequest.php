<?php

declare(strict_types=1);

namespace App\Battle\Http\Web\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class TakeTurnRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'moveSlot' => ['required', 'integer', 'between:1,' . config('game.pokemon.max_active_moves')],
        ];
    }
}
