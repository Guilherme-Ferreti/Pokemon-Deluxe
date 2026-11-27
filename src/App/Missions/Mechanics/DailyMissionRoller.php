<?php

declare(strict_types=1);

namespace App\Missions\Mechanics;

use App\Missions\ValueObjects\NewDailyMission;
use LogicException;
use Shared\Enums\Area;
use Shared\Enums\MissionDifficulty;
use Shared\Enums\MissionGoal;
use Shared\Enums\PokemonType;
use Shared\Helpers\RandomHelper;
use Shared\ValueObjects\MissionReward;

/**
 * @phpstan-import-type MissionRewardArray from MissionReward
 *
 * @phpstan-type DailyMissionTemplate array{difficulty: string, goal: string, description: string, target: array{int, int}, parameters: array<string, string|array{int, int}>, reward: MissionRewardArray, enabled: bool}
 */
final class DailyMissionRoller
{
    /**
     * @return list<NewDailyMission>
     */
    public function roll(): array
    {
        return array_map($this->rollForDifficulty(...), MissionDifficulty::cases());
    }

    private function rollForDifficulty(MissionDifficulty $difficulty): NewDailyMission
    {
        $templates = $this->enabledTemplatesOf($difficulty);

        throw_if($templates === [], LogicException::class, "No daily mission template is enabled for the [{$difficulty->value}] difficulty.");

        $template = RandomHelper::pick($templates);

        $target         = RandomHelper::integer(...$template['target']);
        $goalParameters = [];

        foreach ($template['parameters'] as $name => $choices) {
            $goalParameters[$name] = $this->rollParameter($name, $choices);
        }

        return new NewDailyMission(
            difficulty: $difficulty,
            goal: MissionGoal::from($template['goal']),
            goalParameters: $goalParameters,
            target: $target,
            description: $this->describe($template['description'], $target, $goalParameters),
            reward: MissionReward::fromArray($template['reward']),
        );
    }

    /**
     * @return list<DailyMissionTemplate>
     */
    private function enabledTemplatesOf(MissionDifficulty $difficulty): array
    {
        /** @var list<DailyMissionTemplate> $templates */
        $templates = config('missions.daily_templates');

        return array_values(array_filter(
            $templates,
            fn (array $template): bool => $template['enabled'] && $template['difficulty'] === $difficulty->value,
        ));
    }

    /**
     * @param  string|array{int, int}  $choices
     */
    private function rollParameter(string $name, string|array $choices): string|int
    {
        return match ($name) {
            'type'  => RandomHelper::pick(PokemonType::cases())->value,
            'area'  => RandomHelper::pick(Area::cases())->value,
            'level' => is_array($choices) ? RandomHelper::integer(...$choices) : throw new LogicException('A level parameter needs a [min, max] range.'),
            default => throw new LogicException("Unknown daily mission parameter [{$name}]."),
        };
    }

    /**
     * @param  array<string, string|int>  $goalParameters
     */
    private function describe(string $description, int $target, array $goalParameters): string
    {
        $forms       = explode('|', $description);
        $description = $target === 1 ? $forms[0] : $forms[array_key_last($forms)];

        return strtr($description, [
            '{target}' => (string) $target,
            '{type}'   => isset($goalParameters['type']) ? PokemonType::from((string) $goalParameters['type'])->label() : '{type}',
            '{area}'   => isset($goalParameters['area']) ? Area::from((string) $goalParameters['area'])->label() : '{area}',
            '{level}'  => (string) ($goalParameters['level'] ?? '{level}'),
        ]);
    }
}
