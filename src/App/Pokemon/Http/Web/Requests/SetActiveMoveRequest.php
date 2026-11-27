<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shared\Models\Move;

final class SetActiveMoveRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'moveId' => ['required', 'string', Rule::exists(Move::class, 'id')],
            'slot'   => ['required', 'integer', 'min:1', 'max:' . (int) config('game.pokemon.max_active_moves')],
        ];
    }
}
