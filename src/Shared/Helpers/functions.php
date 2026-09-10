<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
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
     * @return array<string|int, string>
     */
    function enumLabels(string $enum): array
    {
        $labels = Arr::map($enum::cases(), fn (BackedEnum $case) => [
            $case->value => method_exists($case, 'label') ? $case->label() : str($case->name)->headline()->toString(),
        ]);

        return Arr::collapse($labels);
    }
}
