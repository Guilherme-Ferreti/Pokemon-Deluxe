<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use Shared\Interfaces\ValueObject;
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
            $labels[$backedEnum->value] = method_exists($backedEnum, 'label') ? $backedEnum->label() : str($backedEnum->name)->lower()->headline()->toString();
        }

        return $labels;
    }
}

if (! function_exists('value_object_array_contains')) {
    /**
     * @param  array<int, ValueObject>  $array
     */
    function value_object_array_contains(array $array, ValueObject $value): bool
    {
        return array_find($array, fn (ValueObject $item) => $item->equals($value)) instanceof ValueObject;
    }
}

if (! function_exists('value_object_array_diff')) {
    /**
     * Returns the elements of $array1 that are not in $array2
     *
     * @template T of ValueObject
     *
     * @param  array<int, T>  $array1
     * @param  array<int, ValueObject>  $array2
     * @return array<int, T>
     */
    function value_object_array_diff(array $array1, array $array2): array
    {
        return array_filter($array1, fn (ValueObject $item) => ! value_object_array_contains($array2, $item));
    }
}
