<?php

declare(strict_types=1);

namespace Shared\Providers;

use BackedEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Override;
use ReflectionEnum;

final class AppServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->registerLaravelTelescope();
    }

    public function boot(): void
    {
        $this->configureDefaults();

        $this->configureInertiaPagePaths();

        $this->registerCollectionMacros();
    }

    private function registerLaravelTelescope(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    private function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        Model::automaticallyEagerLoadRelationships();

        Model::shouldBeStrict();

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn () => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );

        URL::forceHttps(app()->isProduction());

        Vite::useAggressivePrefetching();
    }

    private function configureInertiaPagePaths(): void
    {
        Inertia::transformComponentUsing(fn (string $name): string => Str::of($name)->replaceFirst('/', '/pages/')->toString());
    }

    private function registerCollectionMacros(): void
    {
        Collection::macro('toUniqueEnums', function (string $enum) {
            /** @var class-string<BackedEnum> $enum */
            $backingType = new ReflectionEnum($enum)->getBackingType()?->getName();

            return $this->unique()
                ->map(fn (mixed $value) => $enum::tryFrom($backingType === 'int' ? (int) $value : (string) $value))
                ->filter()
                ->values();
        });
    }
}
