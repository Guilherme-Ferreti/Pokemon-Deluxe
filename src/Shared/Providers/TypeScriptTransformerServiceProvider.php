<?php

declare(strict_types=1);

namespace Shared\Providers;

use Shared\Helpers\DomainModuleWriter;
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
            ->transformer(new EnumTransformer(useUnionEnums: false))
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
