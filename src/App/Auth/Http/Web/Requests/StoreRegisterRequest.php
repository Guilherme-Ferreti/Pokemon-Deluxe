<?php

declare(strict_types=1);

namespace App\Auth\Http\Web\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRegisterRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
