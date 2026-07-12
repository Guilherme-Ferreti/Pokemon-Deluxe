<?php

declare(strict_types=1);

use App\Providers\TelescopeServiceProvider;
use Shared\Providers\AppServiceProvider;
use Shared\Providers\TypeScriptTransformerServiceProvider;

return [
    TelescopeServiceProvider::class,
    AppServiceProvider::class,
    TypeScriptTransformerServiceProvider::class,
];
