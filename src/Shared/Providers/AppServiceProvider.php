<?php

declare(strict_types=1);

namespace Shared\Providers;

use BackedEnum;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\ExceptionResponse;
use Inertia\Inertia;
use InvalidArgumentException;
use Override;
use ReflectionEnum;
use ReflectionException;
use Shared\Base\DomainException;
use Shared\Enums\InventoryItemType;
use Shared\Helpers\ToastHelper;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\SpecialItem;
use Shared\ValueObjects\IncubatorIndex;
use SplFileInfo;

final class AppServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->registerLaravelTelescope();

        $this->configureListenerDiscovery();
    }

    public function boot(): void
    {
        $this->configureDefaults();

        $this->configureInertiaPagePaths();

        $this->configureInertiaExceptionHandling();

        $this->configureRateLimiting();
        $this->configureRouteBindings();

        $this->configureMorphMap();

        $this->registerCollectionMacros();
    }

    private function registerLaravelTelescope(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    private function configureListenerDiscovery(): void
    {
        DiscoverEvents::guessClassNamesUsing(function (SplFileInfo $file): string {
            $class = Str::of($file->getRealPath())
                ->after(base_path('src') . DIRECTORY_SEPARATOR)
                ->beforeLast('.php')
                ->replace(DIRECTORY_SEPARATOR, '\\')
                ->toString();

            if (! class_exists($class)) {
                throw new ReflectionException("Listener file [{$file->getRealPath()}] holds no class.");
            }

            return $class;
        });
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
        Inertia::transformComponentUsing(function (string $name): string {
            throw_unless(
                Str::contains($name, '::'),
                InvalidArgumentException::class,
                "Inertia page [{$name}] must use the Module::Path/Page format.",
            );

            [$module, $page] = explode('::', $name, 2);

            return "{$module}/pages/{$page}/index";
        });
    }

    private function configureInertiaExceptionHandling(): void
    {
        Inertia::handleExceptionsUsing(function (ExceptionResponse $response): RedirectResponse|ExceptionResponse {
            if ($response->exception instanceof DomainException) {
                ToastHelper::warning('Unable to complete this action', $response->exception->getMessage());

                return back();
            }

            if ($response->exception instanceof ThrottleRequestsException && ! $response->request->expectsJson() && ! $response->request->isPrecognitive()) {
                ToastHelper::warning('Too many attempts', 'Please wait a moment and try again.');

                return back();
            }

            return $response;
        });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by(
            Str::lower($request->string('email')->toString()) . '|' . $request->ip(),
        ));

        RateLimiter::for('registration', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));

        RateLimiter::for('autocomplete', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
    }

    private function configureRouteBindings(): void
    {
        Route::bind('incubatorIndex', function (string $value): IncubatorIndex {
            try {
                return new IncubatorIndex((int) $value);
            } catch (InvalidArgumentException) {
                abort(Response::HTTP_NOT_FOUND);
            }
        });
    }

    private function configureMorphMap(): void
    {
        Relation::morphMap([
            InventoryItemType::POKEBALL->value       => Pokeball::class,
            InventoryItemType::EVOLUTION_ITEM->value => EvolutionItem::class,
            InventoryItemType::TM->value             => Move::class,
            InventoryItemType::SPECIAL_ITEM->value   => SpecialItem::class,
        ]);
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
