<?php

declare(strict_types=1);

namespace Shared\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Shared\ValueObjects\MissionReward;

/**
 * @phpstan-import-type MissionRewardArray from MissionReward
 *
 * @implements CastsAttributes<MissionReward, mixed>
 */
final class AsMissionReward implements CastsAttributes
{
    public function get(Model $model, string $key, $value, array $attributes): MissionReward
    {
        throw_if(! is_string($value), InvalidArgumentException::class, "[{$key}] must be a JSON string.");

        /** @var MissionRewardArray $data */
        $data = json_decode($value, true, flags: JSON_THROW_ON_ERROR);

        return MissionReward::fromArray($data);
    }

    public function set(Model $model, string $key, $value, array $attributes): string
    {
        throw_if(! $value instanceof MissionReward, InvalidArgumentException::class, sprintf("[{$key}] must be an instance of [%s].", MissionReward::class));

        return json_encode($value->toArray(), JSON_THROW_ON_ERROR);
    }
}
