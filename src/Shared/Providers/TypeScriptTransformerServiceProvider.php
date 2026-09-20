<?php

declare(strict_types=1);

namespace Shared\Providers;

use Spatie\LaravelTypeScriptTransformer\RouteFilters\NamedRouteFilter;
use Spatie\LaravelTypeScriptTransformer\TransformedProviders\LaravelRouteTransformedProvider;
use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerApplicationServiceProvider as BaseTypeScriptTransformerServiceProvider;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;

final class TypeScriptTransformerServiceProvider extends BaseTypeScriptTransformerServiceProvider
{
    protected function configure(TypeScriptTransformerConfigFactory $config): void
    {
        $config
            ->transformer(new EnumTransformer(useUnionEnums: false))
            ->transformDirectories(base_path('src/App'), base_path('src/Shared'))
            ->writer(new FlatModuleWriter('php-types.ts'))
            ->outputDirectory(resource_path('js/shared/types'))
            ->formatter(PrettierFormatter::class)
            ->provider(new LaravelRouteTransformedProvider(
                filters: [
                    new NamedRouteFilter('boost.*', 'telescope'),
                ],
                absoluteUrlsByDefault: false
            ));
    }
}
