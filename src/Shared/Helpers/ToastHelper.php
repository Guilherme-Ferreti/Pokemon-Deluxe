<?php

declare(strict_types=1);

namespace Shared\Helpers;

use Inertia\Inertia;

final class ToastHelper
{
    public static function success(string $title, string $message): void
    {
        Inertia::flash('toast', [
            'type'    => 'success',
            'title'   => $title,
            'message' => $message,
        ]);
    }

    public static function error(string $title, string $message): void
    {
        Inertia::flash('toast', [
            'type'    => 'error',
            'title'   => $title,
            'message' => $message,
        ]);
    }

    public static function info(string $title, string $message): void
    {
        Inertia::flash('toast', [
            'type'    => 'info',
            'title'   => $title,
            'message' => $message,
        ]);
    }

    public static function warning(string $title, string $message): void
    {
        Inertia::flash('toast', [
            'type'    => 'warning',
            'title'   => $title,
            'message' => $message,
        ]);
    }
}
