<?php

declare(strict_types=1);

namespace App\Missions\Actions;

use App\Missions\Mechanics\DailyMissionRoller;
use App\Missions\ValueObjects\NewDailyMission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Shared\Enums\MissionGoal;
use Shared\Enums\MissionKind;
use Shared\Models\Achievement;
use Shared\Models\Pokemon;
use Shared\Models\User;
use Shared\Models\UserMission;

final class EnsureDailyMissionsAction
{
    private User $user;

    /**
     * @return Collection<int, UserMission>
     */
    public function handle(User $user): Collection
    {
        $this->user = $user;

        return DB::transaction(function (): Collection {
            User::query()->whereKey($this->user->id)->lockForUpdate()->firstOrFail();

            $this->createMissingAchievementMissions();

            $todaysDailies = $this->loadTodaysDailies();

            if ($todaysDailies->isEmpty()) {
                $this->createTodaysDailies();

                $todaysDailies = $this->loadTodaysDailies();
            }

            return $todaysDailies;
        });
    }

    private function createMissingAchievementMissions(): void
    {
        $missingAchievements = Achievement::query()
            ->whereNotIn('id', UserMission::query()->whereBelongsTo($this->user)->whereNotNull('achievement_id')->select('achievement_id'))
            ->orderBy('sort_order')
            ->get();

        foreach ($missingAchievements as $achievement) {
            $progress = $this->startingProgress($achievement->goal, $achievement->target);

            UserMission::query()->create([
                'user_id'         => $this->user->id,
                'kind'            => MissionKind::ACHIEVEMENT,
                'achievement_id'  => $achievement->id,
                'description'     => $achievement->name,
                'goal'            => $achievement->goal,
                'goal_parameters' => $achievement->goal_parameters,
                'target'          => $achievement->target,
                'progress'        => $progress,
                'reward'          => $achievement->reward,
                'completed_at'    => $progress >= $achievement->target ? now() : null,
            ]);
        }
    }

    private function createTodaysDailies(): void
    {
        foreach (new DailyMissionRoller()->roll() as $newDailyMission) {
            $this->createDaily($newDailyMission);
        }
    }

    private function createDaily(NewDailyMission $newDailyMission): void
    {
        $progress = $this->startingProgress($newDailyMission->goal, $newDailyMission->target);

        UserMission::query()->create([
            'user_id'         => $this->user->id,
            'kind'            => MissionKind::DAILY,
            'date'            => today(),
            'difficulty'      => $newDailyMission->difficulty,
            'description'     => $newDailyMission->description,
            'goal'            => $newDailyMission->goal,
            'goal_parameters' => $newDailyMission->goalParameters,
            'target'          => $newDailyMission->target,
            'progress'        => $progress,
            'reward'          => $newDailyMission->reward,
            'completed_at'    => $progress >= $newDailyMission->target ? now() : null,
        ]);
    }

    private function startingProgress(MissionGoal $goal, int $target): int
    {
        $bestValue = match ($goal) {
            MissionGoal::REACH_LEVEL => (int) Pokemon::query()->whereBelongsTo($this->user, 'user')->max('level'),
            MissionGoal::REACH_BOOST => (int) Pokemon::query()->whereBelongsTo($this->user, 'user')->max('boost'),
            default                  => 0,
        };

        return min($bestValue, $target);
    }

    /**
     * @return Collection<int, UserMission>
     */
    private function loadTodaysDailies(): Collection
    {
        return UserMission::query()
            ->whereBelongsTo($this->user)
            ->where('kind', MissionKind::DAILY)
            ->whereDate('date', today())
            ->get()
            ->sortBy(fn (UserMission $mission): int => $mission->difficulty?->sortOrder() ?? 0)
            ->values();
    }
}
