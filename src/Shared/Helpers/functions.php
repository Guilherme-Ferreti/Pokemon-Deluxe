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
