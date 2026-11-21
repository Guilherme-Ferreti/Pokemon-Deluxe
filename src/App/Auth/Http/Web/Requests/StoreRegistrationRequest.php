<?php

declare(strict_types=1);

namespace App\Auth\Http\Web\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\Password;
use Override;

final class StoreRegistrationRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string|Password|In>>
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::defaults(), 'same:passwordConfirmation'],
            'starter'  => ['required', 'string', Rule::in(config('game.registration.starters'))],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function messages(): array
    {
        return [
            'starter.required' => 'Choose a starter to continue.',
            'starter.in'       => 'Choose one of the starters shown.',
        ];
    }
}
