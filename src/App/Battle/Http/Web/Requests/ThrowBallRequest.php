<?php

declare(strict_types=1);

namespace App\Battle\Http\Web\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Shared\Models\Pokeball;

final class ThrowBallRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pokeball' => ['required', 'string', Rule::exists(Pokeball::class, 'slug')],
        ];
    }
}
