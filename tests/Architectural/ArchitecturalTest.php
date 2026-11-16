<?php

declare(strict_types=1);

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Lottery;
use Illuminate\Support\ServiceProvider;
use Inertia\ProvidesInertiaProperties;
use Shared\Base\Controller;
use Shared\Base\DomainException;
use Shared\Helpers\RandomHelper;
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

arch('Events')
    ->expect('App\*\Events')
    ->toBeClasses()
    ->toBeFinal()
    ->toBeReadonly()
    ->toExtendNothing()
    ->toUseTrait(Dispatchable::class)
    ->toImplement(ShouldDispatchAfterCommit::class);

arch('Listeners')
    ->expect('App\*\Listeners')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtendNothing()
    ->toHaveMethod('handle');

arch('Data')
    ->expect('App\*\Data')
    ->toHaveSuffix('Data')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtendNothing()
    ->toImplementNothing()
    ->not->toHaveProtectedMethods();

arch('Pipes')
    ->expect('App\*\Pipes')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtendNothing()
    ->toImplementNothing()
    ->toHaveMethod('handle')
    ->not->toHavePublicMethodsBesides('handle')
    ->not->toHaveProtectedMethods();

arch('Mechanics')
    ->expect('App\*\Mechanics')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtendNothing()
    ->not->toUse([
        DB::class,
        'Illuminate\Http',
        Auth::class,
    ]);

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

arch('Shared Exceptions')
    ->expect('Shared\Exceptions')
    ->toHaveSuffix('Exception')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(RuntimeException::class);

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
    ->expect([
        'App\*\ValueObjects',
        'Shared\ValueObjects',
    ])
    ->toBeClasses()
    ->toBeFinal()
    ->toBeReadOnly()
    ->toImplement(ValueObject::class)
    ->toHaveConstructor();

$randomnessOutsideRandomHelper = [
    'rand',
    'mt_rand',
    'random_int',
    'array_rand',
    'shuffle',
    Lottery::class,
];

arch('App gets randomness only from RandomHelper')
    ->expect('App')
    ->not->toUse($randomnessOutsideRandomHelper);

arch('Shared gets randomness only from RandomHelper')
    ->expect('Shared')
    ->not->toUse($randomnessOutsideRandomHelper)
    ->ignoring(RandomHelper::class);
