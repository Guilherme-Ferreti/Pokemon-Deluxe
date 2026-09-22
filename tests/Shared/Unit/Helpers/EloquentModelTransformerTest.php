<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Shared\Enums\ElementalType;
use Shared\Enums\Generation;
use Shared\Helpers\Typescript\EloquentModelTransformer;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\ValueObjects\SpeciesPortraits;
use Shared\ValueObjects\SpeciesSprites;
use Spatie\TypeScriptTransformer\Data\TransformationContext;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformed\Transformed;
use Spatie\TypeScriptTransformer\Transformed\Untransformable;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptAlias;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIndexSignature;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNull;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptString;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptUnion;

final class UnmarkedModelFixture extends Model {}

function transformEloquentModel(string $class): Transformed|Untransformable
{
    $node = PhpClassNode::fromClassString($class);

    return (new EloquentModelTransformer)->transform(
        $node,
        TransformationContext::createFromPhpClass($node),
    );
}

function transformEloquentModelOrFail(string $class): Transformed
{
    $transformed = transformEloquentModel($class);

    if (! $transformed instanceof Transformed) {
        throw new RuntimeException("Expected {$class} to be transformed.");
    }

    return $transformed;
}

/**
 * @return array<TypeScriptProperty>
 */
function transformEloquentModelProperties(string $class): array
{
    $node = transformEloquentModelOrFail($class)->getNode();

    if (! $node instanceof TypeScriptAlias) {
        throw new RuntimeException("Expected {$class} to produce an alias.");
    }

    if (! $node->type instanceof TypeScriptObject) {
        throw new RuntimeException("Expected {$class} to produce an object.");
    }

    return $node->type->properties;
}

function propertyName(TypeScriptProperty $property): string
{
    if ($property->name instanceof TypeScriptIndexSignature) {
        return $property->name->name;
    }

    return $property->name->getName();
}

/**
 * @param  array<TypeScriptProperty>  $properties
 * @return array<string>
 */
function modelPropertyNames(array $properties): array
{
    return array_map(
        fn (TypeScriptProperty $property) => propertyName($property),
        $properties,
    );
}

/**
 * @param  array<TypeScriptProperty>  $properties
 */
function findProperty(array $properties, string $name): TypeScriptProperty
{
    foreach ($properties as $property) {
        if (propertyName($property) === $name) {
            return $property;
        }
    }

    throw new RuntimeException("Property {$name} not found.");
}

/**
 * @template T of object
 *
 * @param  class-string<T>  $expected
 * @return T
 */
function narrowNode(object $node, string $expected): object
{
    if (! $node instanceof $expected) {
        throw new RuntimeException("Expected a {$expected} node.");
    }

    return $node;
}

it('transforms a model marked with the TypeScript attribute', function () {
    $transformed = transformEloquentModelOrFail(Species::class);

    expect($transformed->getName())->toBe('Species')
        ->and($transformed->getLocation())->toBe(['Shared', 'Models']);
});

it('does not transform a model without the TypeScript attribute', function () {
    expect(transformEloquentModel(UnmarkedModelFixture::class))->toBeInstanceOf(Untransformable::class);
});

it('does not transform a class that is not an Eloquent model', function () {
    expect(transformEloquentModel(SpeciesSprites::class))->toBeInstanceOf(Untransformable::class);
});

it('does not transform an enum', function () {
    expect(transformEloquentModel(ElementalType::class))->toBeInstanceOf(Untransformable::class);
});

it('uses the model name for the generated type', function () {
    $transformed = transformEloquentModelOrFail(Move::class);

    expect($transformed->getName())->toBe('Move');
});

it('transforms @property names into camelCase', function () {
    $names = modelPropertyNames(transformEloquentModelProperties(Species::class));

    expect($names)->toContain(
        'pokedexNumber',
        'captureRate',
        'genderRate',
        'textEntry',
        'isBaby',
    )->not->toContain(
        'pokedex_number',
        'capture_rate',
        'gender_rate',
        'text_entry',
        'is_baby',
    );
});

it('ignores @property-read relationship annotations', function () {
    $properties = transformEloquentModelProperties(Species::class);

    expect(modelPropertyNames($properties))->not->toContain(
        'evolutions',
        'previousEvolutions',
        'learnableMoves',
        'pokemon',
    );
});

it('unwraps collection annotations into arrays', function () {
    $types = narrowNode(
        findProperty(transformEloquentModelProperties(Species::class), 'types')->type,
        TypeScriptArray::class,
    );
    $element   = narrowNode($types->types[0], TypeScriptReference::class);
    $reference = narrowNode($element->reference, ClassStringReference::class);

    expect($types->types)->toHaveCount(1)
        ->and($reference->classString)->toBe(ElementalType::class);
});

it('keeps enum annotations as references', function () {
    $generation = narrowNode(
        findProperty(transformEloquentModelProperties(Species::class), 'generation')->type,
        TypeScriptReference::class,
    );
    $reference = narrowNode($generation->reference, ClassStringReference::class);

    expect($reference->classString)->toBe(Generation::class);
});

it('inlines value objects into the model type', function () {
    $properties = transformEloquentModelProperties(Species::class);

    $hasValueObjectReference = collect($properties)->contains(
        fn (TypeScriptProperty $property) => $property->type instanceof TypeScriptReference
            && $property->type->reference instanceof ClassStringReference
            && in_array($property->type->reference->classString, [SpeciesSprites::class, SpeciesPortraits::class], true),
    );

    expect($hasValueObjectReference)->toBeFalse();
});

it('inlines value objects from their promoted constructor properties', function () {
    $properties = transformEloquentModelProperties(Species::class);

    $sprites     = narrowNode(findProperty($properties, 'sprites')->type, TypeScriptObject::class);
    $portraits   = narrowNode(findProperty($properties, 'portraits')->type, TypeScriptObject::class);
    $backDefault = narrowNode($sprites->properties[0]->type, TypeScriptUnion::class);

    expect(modelPropertyNames($sprites->properties))->toBe([
        'backDefault',
        'backFemale',
        'backShiny',
        'backShinyFemale',
        'frontDefault',
        'frontFemale',
        'frontShiny',
        'frontShinyFemale',
    ])
        ->and(propertyName($sprites->properties[0]))->toBe('backDefault')
        ->and($backDefault->contains(fn (TypeScriptNode $type) => $type instanceof TypeScriptString))->toBeTrue()
        ->and($backDefault->contains(fn (TypeScriptNode $type) => $type instanceof TypeScriptNull))->toBeTrue()
        ->and(modelPropertyNames($portraits->properties))->toBe(['regular', 'shiny']);
});
