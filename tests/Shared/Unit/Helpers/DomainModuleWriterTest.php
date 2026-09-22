<?php

declare(strict_types=1);

use App\Dex\Enums\SearchPokedexSortBy;
use Shared\Enums\Ailment;
use Shared\Enums\ElementalType;
use Shared\Helpers\Typescript\DomainModuleWriter;
use Shared\Models\Species;
use Spatie\TypeScriptTransformer\Collections\TransformedCollection;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptEnum;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;

function makeTransformedEnum(DomainModuleWriter $writer, string $class, string $name): Transformed
{
    return new Transformed(
        new TypeScriptEnum($name, [['name' => 'DEFAULT', 'value' => 'default']]),
        new ClassStringReference($class),
        explode('\\', $class),
        true,
        $writer,
    );
}

it('writes shared enums into the shared types enums directory', function () {
    $writer = new DomainModuleWriter;

    $ailment = makeTransformedEnum($writer, Ailment::class, 'Ailment');
    $helpers = $writer->output([$ailment], new TransformedCollection([$ailment]));

    expect($helpers)->toHaveCount(1)
        ->and($helpers[0]->path)->toBe('shared/types/enums/Ailment.ts');
});

it('writes app enums into their domain types directory', function () {
    $writer = new DomainModuleWriter;

    $sortBy = makeTransformedEnum($writer, SearchPokedexSortBy::class, 'SearchPokedexSortBy');
    $files  = $writer->output([$sortBy], new TransformedCollection([$sortBy]));

    expect($files)->toHaveCount(1)
        ->and($files[0]->path)->toBe('app/Dex/types/enums/SearchPokedexSortBy.ts');
});

it('writes models into the shared types models directory', function () {
    $writer = new DomainModuleWriter;

    $species = makeTransformedEnum($writer, Species::class, 'Species');
    $files   = $writer->output([$species], new TransformedCollection([$species]));

    expect($files)->toHaveCount(1)
        ->and($files[0]->path)->toBe('shared/types/models/Species.ts');
});

it('derives a kebab-cased directory from the type category', function () {
    $writer = new DomainModuleWriter;

    $valueObject = makeTransformedEnum($writer, 'App\Pokemon\ValueObjects\SearchBoxFilter', 'SearchBoxFilter');
    $files       = $writer->output([$valueObject], new TransformedCollection([$valueObject]));

    expect($files)->toHaveCount(1)
        ->and($files[0]->path)->toBe('app/Pokemon/types/value-objects/SearchBoxFilter.ts');
});

it('imports referenced types using a relative path', function () {
    $writer = new DomainModuleWriter;

    $typeReference = TypeScriptReference::referencingPhpClass(ElementalType::class);

    $box = new Transformed(
        new TypeScriptAlias(new TypeScriptIdentifier('Box'), $typeReference),
        new ClassStringReference('Shared\Models\Box'),
        ['Shared', 'Models'],
        true,
        $writer,
    );

    $elementalType = makeTransformedEnum($writer, ElementalType::class, 'ElementalType');

    $box->references($elementalType->getReference()->getKey(), $typeReference);
    $elementalType->referencedBy($box->getReference()->getKey());
    $typeReference->connect($elementalType);

    $files = $writer->output([$box], new TransformedCollection([$box, $elementalType]));

    expect($files)->toHaveCount(1)
        ->and($files[0]->path)->toBe('shared/types/models/Box.ts')
        ->and($files[0]->contents)->toContain("import { ElementalType } from '@/shared/types/enums/ElementalType';")
        ->and($files[0]->contents)->toContain(
            "import { ElementalType } from '@/shared/types/enums/ElementalType';\n\nexport type Box = ElementalType;"
        )
        ->and($files[0]->contents)->toContain('type Box = ElementalType;');
});

it('resolves nested imports to their @ alias', function () {
    $writer = new DomainModuleWriter;

    $typeReference = TypeScriptReference::referencingPhpClass(ElementalType::class);

    $box = new Transformed(
        new TypeScriptAlias(new TypeScriptIdentifier('Box'), $typeReference),
        new ClassStringReference('App\Dex\Models\Box'),
        ['App', 'Dex', 'Models'],
        true,
        $writer,
    );

    $elementalType = makeTransformedEnum($writer, ElementalType::class, 'ElementalType');

    $box->references($elementalType->getReference()->getKey(), $typeReference);
    $elementalType->referencedBy($box->getReference()->getKey());
    $typeReference->connect($elementalType);

    $files = $writer->output([$box], new TransformedCollection([$box, $elementalType]));

    expect($files)->toHaveCount(1)
        ->and($files[0]->path)->toBe('app/Dex/types/models/Box.ts')
        ->and($files[0]->contents)->toContain(
            "import { ElementalType } from '@/shared/types/enums/ElementalType';"
        )
        ->and($files[0]->contents)->not->toContain('../../');
});

it('merges types that resolve to the same file into one module', function () {
    $writer = new DomainModuleWriter;

    $first  = makeTransformedEnum($writer, 'Shared\Enums\First', 'Duplicate');
    $second = makeTransformedEnum($writer, 'Shared\Enums\Second', 'Duplicate');

    $files = $writer->output([$first, $second], new TransformedCollection([$first, $second]));

    expect($files)->toHaveCount(1)
        ->and($files[0]->path)->toBe('shared/types/enums/Duplicate.ts')
        ->and(substr_count($files[0]->contents, 'enum Duplicate'))->toBe(2);
});
