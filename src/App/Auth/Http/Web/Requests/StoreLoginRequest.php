<?php

declare(strict_types=1);

namespace App\Auth\Http\Web\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreLoginRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }
}
