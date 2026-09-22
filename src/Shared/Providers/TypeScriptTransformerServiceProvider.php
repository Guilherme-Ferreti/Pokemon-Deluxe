<?php

declare(strict_types=1);

namespace Shared\Providers;

use Carbon\Carbon;
use Shared\Helpers\Typescript\DomainModuleWriter;
use Shared\Helpers\Typescript\EloquentModelTransformer;
use Spatie\LaravelTypeScriptTransformer\RouteFilters\NamedRouteFilter;
use Spatie\LaravelTypeScriptTransformer\TransformedProviders\LaravelRouteTransformedProvider;
use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerApplicationServiceProvider as BaseTypeScriptTransformerServiceProvider;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

final class TypeScriptTransformerServiceProvider extends BaseTypeScriptTransformerServiceProvider
{
    protected function configure(TypeScriptTransformerConfigFactory $config): void
    {
        $config
            ->transformer(
                new EnumTransformer(useUnionEnums: false),
                new EloquentModelTransformer,
            )
            ->replaceType(Carbon::class, 'string')
            ->transformDirectories(base_path('src/App'), base_path('src/Shared'))
            ->writer(new DomainModuleWriter)
            ->outputDirectory(resource_path('js'))
            ->formatter(PrettierFormatter::class)
            ->provider(new LaravelRouteTransformedProvider(
                filters: [
                    new NamedRouteFilter('boost.*', 'telescope'),
                ],
                path: 'shared/types/helpers/route.ts',
                absoluteUrlsByDefault: false
            ));
    }
}
