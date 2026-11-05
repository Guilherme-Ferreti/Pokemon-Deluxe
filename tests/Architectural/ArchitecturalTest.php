<?php

declare(strict_types=1);

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\ServiceProvider;
use Inertia\ProvidesInertiaProperties;
use Shared\Base\Controller;
use Shared\Base\DomainException;
use Shared\Interfaces\Filter;
use Shared\Interfaces\ValueObject;

arch()->preset()->php();
arch()->preset()->security();
arch()->expect(['dd', 'dump', 'sleep'])->toBeUsedInNothing();

arch('App & Shared')
    ->expect(['App', 'Shared'])
    ->toBeCasedCorrectly()
    ->toUseStrictTypes()
    ->toUseStrictEquality();

arch('Controllers')
    ->expect([
        'App\*\Http\Web\Controllers',
        'App\*\Http\Api\Controllers',
    ])
    ->toHaveSuffix('Controller')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(Controller::class)
    ->toImplementNothing()
    ->not->toHavePublicMethodsBesides(['__invoke', 'index', 'show', 'create', 'store', 'edit', 'update', 'delete', 'destroy'])
    ->not->toHavePrivateMethods()
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
    ->toHaveSuffix('Action')
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

arch('Exceptions')
    ->expect('App\*\Exceptions')
    ->toHaveSuffix('Exception')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(DomainException::class);

arch('Domain Middlewares')
    ->expect('App\*\Http\Web\Middlewares')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtendNothing()
    ->toHaveMethod('handle');

arch('Support')
    ->expect('App\*\Support')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtendNothing()
    ->toImplementNothing()
    ->not->toHaveProtectedMethods();

arch('Services')
    ->expect('App\*\Services')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtendNothing()
    ->not->toHaveProtectedMethods();

arch('Base')
    ->expect('Shared\Base')
    ->toBeClasses()
    ->toBeAbstract();

arch('Casts')
    ->expect('Shared\Casts')
    ->toHavePrefix('As')
    ->toBeClasses()
    ->toBeFinal()
    ->toImplement(CastsAttributes::class);

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
    ->toBeReadOnly()
    ->toImplement(ValueObject::class)
    ->toHaveConstructor();
