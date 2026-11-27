<?php

declare(strict_types=1);

namespace App\Pokemon\Http\Web\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReorderActiveMovesRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'moveIds'   => ['required', 'array', 'min:1', 'max:' . (int) config('game.pokemon.max_active_moves')],
            'moveIds.*' => ['required', 'string', 'distinct'],
        ];
    }
}
