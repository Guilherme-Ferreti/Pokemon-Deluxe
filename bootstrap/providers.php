<?php

declare(strict_types=1);

use Shared\Providers\AppServiceProvider;
use Shared\Providers\TypeScriptTransformerServiceProvider;

return [
    AppServiceProvider::class,
    TypeScriptTransformerServiceProvider::class,
];
