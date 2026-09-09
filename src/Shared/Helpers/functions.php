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
