<?php

declare(strict_types=1);

namespace Shared\Helpers;

use Illuminate\Support\Str;
use Spatie\TypeScriptTransformer\Actions\ResolveImportsAndResolvedReferenceMapAction;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\Data\ModuleImportResolvedReference;
use Spatie\TypeScriptTransformer\Data\WriteableFile;
use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Writers\Writer;

final readonly class DomainModuleWriter implements Writer
{
    public function __construct(
        private string $typesDirectory = 'types',
        private ResolveImportsAndResolvedReferenceMapAction $resolveImportsAction = new ResolveImportsAndResolvedReferenceMapAction,
    ) {}

    /**
     * @param  array<Transformed>  $transformed
     * @return array<WriteableFile>
     */
    public function output(array $transformed, TransformedCollection $transformedCollection): array
    {
        $grouped = [];

        foreach ($transformed as $item) {
            $grouped[$this->resolveRelativePath($item)][] = $item;
        }

        ksort($grouped);

        $files = [];

        foreach ($grouped as $path => $items) {
            usort($items, fn (Transformed $a, Transformed $b) => $a->getName() <=> $b->getName());

            $files[] = $this->writeLocation($path, $items, $transformedCollection);
        }

        return $files;
    }

    public function resolveReference(Transformed $transformed): ModuleImportResolvedReference
    {
        return new ModuleImportResolvedReference(
            $transformed->getName(),
            $this->resolveRelativePath($transformed),
        );
    }

    /**
     * @param  array<Transformed>  $items
     */
    private function writeLocation(string $path, array $items, TransformedCollection $transformedCollection): WriteableFile
    {
        [$imports, $resolvedReferenceMap] = $this->resolveImportsAction->execute($path, $items, $transformedCollection);

        $output = '';

        $writingContext = new WritingContext($resolvedReferenceMap);

        foreach ($imports->getTypeScriptNodes() as $typeScriptImport) {
            $output .= $typeScriptImport->write($writingContext) . "\n";
        }

        foreach ($items as $item) {
            $output .= $item->write($writingContext) . "\n";
        }

        return new WriteableFile($path, $output);
    }

    private function resolveRelativePath(Transformed $transformed): string
    {
        $category = $this->resolveCategory($transformed);

        return $category === null
            ? sprintf('%s/%s/%s.ts', $this->resolveDomain($transformed), $this->typesDirectory, $transformed->getName())
            : sprintf('%s/%s/%s/%s.ts', $this->resolveDomain($transformed), $this->typesDirectory, $category, $transformed->getName());
    }

    private function resolveDomain(Transformed $transformed): string
    {
        $namespaceSegments = $transformed->getLocation();

        if (($namespaceSegments[0] ?? null) === 'App' && isset($namespaceSegments[1])) {
            return 'app/' . Str::ucfirst($namespaceSegments[1]);
        }

        return 'shared';
    }

    private function resolveCategory(Transformed $transformed): ?string
    {
        $namespaceSegments = $transformed->getLocation();

        $category = ($namespaceSegments[0] ?? null) === 'App'
            ? ($namespaceSegments[2] ?? null)
            : ($namespaceSegments[1] ?? null);

        return $category === null ? null : Str::kebab($category);
    }
}
