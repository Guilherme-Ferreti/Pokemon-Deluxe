<?php

declare(strict_types=1);

namespace Shared\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Shared\ValueObjects\BattleState;

/**
 * @phpstan-import-type BattleStateArray from BattleState
 *
 * @implements CastsAttributes<BattleState, mixed>
 */
final class AsBattleState implements CastsAttributes
{
    public function get(Model $model, string $key, $value, array $attributes): BattleState
    {
        throw_if(! is_string($value), InvalidArgumentException::class, "[{$key}] must be a JSON string.");

        /** @var BattleStateArray $data */
        $data = json_decode($value, true, flags: JSON_THROW_ON_ERROR);

        return BattleState::fromArray($data);
    }

    public function set(Model $model, string $key, $value, array $attributes): string
    {
        throw_if(! $value instanceof BattleState, InvalidArgumentException::class, sprintf("[{$key}] must be an instance of [%s].", BattleState::class));

        return json_encode($value->toArray(), JSON_THROW_ON_ERROR);
    }
}
