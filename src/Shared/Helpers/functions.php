<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use Shared\Models\User;

if (! function_exists('currentUser')) {
    function currentUser(): User
    {
        return Auth::user();
    }
}

if (! function_exists('toIntIfWhole')) {
    function toIntIfWhole(float|int $value): float|int
    {
        return fmod($value, 1.0) === 0.0 ? (int) $value : $value;
    }
}

if (! function_exists('enumLabels')) {
    /**
     * @param  enum-string<BackedEnum>  $enum
     * @return array<string|int, string>
     */
    function enumLabels(string $enum): array
    {
        $labels = [];

        foreach ($enum::cases() as $backedEnum) {
            $labels[$backedEnum->value] = method_exists($backedEnum, 'label') ? $backedEnum->label() : str($backedEnum->name)->headline()->toString();
        }

        return $labels;
    }
}
