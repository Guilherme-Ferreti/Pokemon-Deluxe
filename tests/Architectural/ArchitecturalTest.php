<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\ServiceProvider;
use Inertia\ProvidesInertiaProperties;
use Shared\Base\Controller;
use Shared\Interfaces\Filter;

arch()->preset()->php();
arch()->preset()->security();
arch()->expect(['dd', 'dump', 'sleep'])->toBeUsedInNothing();

arch('App & Shared')
    ->expect(['App', 'Shared'])
    ->toBeCasedCorrectly()
    ->toUseStrictTypes()
    ->toUseStrictEquality();

arch('Controllers')
    ->expect('App\*\Http\Web\Controllers')
    ->toHaveSuffix('Controller')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(Controller::class)
    ->toImplementNothing()
    ->not->toHavePublicMethodsBesides(['__invoke', 'index', 'show', 'create', 'store', 'edit', 'update', 'delete', 'destroy'])
    ->not->toHaveProtectedMethods();

arch('Form Requests')
    ->expect('App\*\Http\Web\Requests')
    ->toHaveSuffix('Request')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(FormRequest::class)
    ->toHaveMethod('rules')
    ->not->toHavePublicMethodsBesides('rules')
    ->not->toHaveProtectedMethods()
    ->toHaveMethodsDocumented();

arch('Actions')
    ->expect('App\*\Actions')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtendNothing()
    ->toImplementNothing()
    ->toHaveMethod('handle')
    ->not->toHavePublicMethodsBesides('handle')
    ->not->toHaveProtectedMethods();

arch('PageProps')
    ->expect('App\*\Http\Web\PageProps')
    ->toHaveSuffix('PageProps')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtendNothing()
    ->toImplement(ProvidesInertiaProperties::class)
    ->toHaveMethod('toInertiaProperties')
    ->not->toHavePublicMethodsBesides(['__construct', 'toInertiaProperties'])
    ->not->toHaveProtectedMethods();

arch('Filters')
    ->expect('App\*\Filters')
    ->toHaveSuffix('Filters')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtendNothing()
    ->toImplement(Filter::class)
    ->toHaveConstructor();

arch('Queries')
    ->expect('App\*\Queries')
    ->toHaveSuffix('Query')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtendNothing()
    ->toImplementNothing()
    ->toHaveMethod('handle')
    ->not->toHavePublicMethodsBesides('handle')
    ->not->toHaveProtectedMethods();

arch('Cache Keys')
    ->expect('App\*\CacheKeys')
    ->toHaveSuffix('CacheKeys')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtendNothing()
    ->toImplementNothing()
    ->toUseNothing()
    ->not->toHaveProtectedMethods()
    ->not->toHavePrivateMethods();

arch('Base')
    ->expect('Shared\Base')
    ->toBeClasses()
    ->toBeAbstract();

arch('Enums')
    ->expect([
        'App\*\Enums',
        'Shared\Enums',
    ])
    ->toBeEnums();

arch('Helpers')
    ->expect('Shared\Helpers')
    ->toHaveSuffix('Helper')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtendNothing()
    ->toImplementNothing()
    ->not->toHaveProtectedMethods()
    ->not->toHavePrivateMethods();

arch('Interfaces')
    ->expect('Shared\Interfaces')
    ->toBeInterfaces();

arch('Middlewares')
    ->expect('Shared\Middlewares')
    ->toBeClasses()
    ->toBeFinal();

arch('Models')
    ->expect('Shared\Models')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(Model::class);

arch('Service Providers')
    ->expect('Shared\Providers')
    ->toHaveSuffix('ServiceProvider')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(ServiceProvider::class);

arch('Value Objects')
    ->expect('Shared\ValueObjects')
    ->toBeClasses()
    ->toBeFinal()
    ->toHaveConstructor();
