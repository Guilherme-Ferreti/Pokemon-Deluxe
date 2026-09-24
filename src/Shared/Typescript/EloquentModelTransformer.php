<?php

declare(strict_types=1);

namespace Shared\Typescript;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Data\TransformationContext;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\References\ClassStringReference;
use Spatie\TypeScriptTransformer\Transformers\ClassTransformer;
use Spatie\TypeScriptTransformer\TypeResolvers\Data\ParsedClass;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptArray;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptGeneric;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptIdentifier;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;
use Spatie\TypeScriptTransformer\Visitor\Visitor;
use Spatie\TypeScriptTransformer\Visitor\VisitorOperation;
use Throwable;

final class EloquentModelTransformer extends ClassTransformer
{
    private const string VALUE_OBJECT_NAMESPACE = 'Shared\\ValueObjects\\';

    protected function shouldTransform(PhpClassNode $phpClassNode): bool
    {
        return count($phpClassNode->getAttributes(TypeScript::class)) > 0
            && $phpClassNode->getReflection(Model::class);
    }

    protected function getTypeScriptNode(
        PhpClassNode $phpClassNode,
        TransformationContext $context,
        ?ParsedClass $parsedClass = null,
    ): TypeScriptNode {
        $properties = [];

        $hiddenAttributes  = $this->getHiddenAttributes($phpClassNode);
        $visibleAttributes = $this->getVisibleAttributes($phpClassNode);

        foreach ($parsedClass->properties ?? [] as $name => $annotation) {
            if ($this->isHiddenAttribute($name, $hiddenAttributes, $visibleAttributes)) {
                continue;
            }

            $type = $this->transpilePhpStanTypeToTypeScriptTypeAction->execute(
                $annotation->type,
                $phpClassNode,
                $parsedClass->templates ?? [],
            );

            $properties[] = new TypeScriptProperty(
                Str::camel($name),
                $this->inlineValueObjects($this->listShapesToTuples($this->unwrapCollections($type))),
            );
        }

        return new TypeScriptObject($properties);
    }

    /**
     * @return array<int, string>
     */
    private function getHiddenAttributes(PhpClassNode $phpClassNode): array
    {
        return $this->modelAttributes($phpClassNode, 'getHidden');
    }

    /**
     * @return array<int, string>
     */
    private function getVisibleAttributes(PhpClassNode $phpClassNode): array
    {
        return $this->modelAttributes($phpClassNode, 'getVisible');
    }

    /**
     * @return array<int, string>
     */
    private function modelAttributes(PhpClassNode $phpClassNode, string $method): array
    {
        $model = $this->instantiateModel($phpClassNode);

        if (!$model instanceof Model) {
            return [];
        }

        return array_values($model->{$method}());
    }

    private function instantiateModel(PhpClassNode $phpClassNode): ?Model
    {
        $className = $phpClassNode->getName();

        if (! is_subclass_of($className, Model::class) || ! class_exists($className)) {
            return null;
        }

        try {
            return new $className;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array<int, string>  $hiddenAttributes
     * @param  array<int, string>  $visibleAttributes
     */
    private function isHiddenAttribute(string $attribute, array $hiddenAttributes, array $visibleAttributes): bool
    {
        if (in_array($attribute, $hiddenAttributes, true)) {
            return true;
        }

        return $visibleAttributes !== [] && ! in_array($attribute, $visibleAttributes, true);
    }

    private function listShapesToTuples(TypeScriptNode $type): TypeScriptNode
    {
        $convert = Visitor::create()->before(function (TypeScriptObject $object): VisitorOperation {
            $expectedIndex = 0;

            foreach ($object->properties as $property) {
                $name = $property->name instanceof TypeScriptIdentifier
                    ? $property->name->name
                    : null;

                if ($name === null || (string) $expectedIndex !== $name) {
                    return VisitorOperation::keep();
                }

                $expectedIndex++;
            }

            if ($expectedIndex === 0) {
                return VisitorOperation::keep();
            }

            return VisitorOperation::replace(new TypeScriptTuple(
                array_map(fn (TypeScriptProperty $property) => $property->type, $object->properties),
                array_map(fn (TypeScriptProperty $property) => $property->isOptional, $object->properties),
            ));
        }, [TypeScriptObject::class]);

        return $convert->execute($type);
    }

    private function unwrapCollections(TypeScriptNode $type): TypeScriptNode
    {
        $unwrap = Visitor::create()->before(function (TypeScriptGeneric $generic): VisitorOperation {
            $reference = $generic->type instanceof TypeScriptReference
                ? $generic->type->reference
                : null;

            $isCollection = $reference instanceof ClassStringReference
                && in_array($reference->classString, [Collection::class, EloquentCollection::class], true);

            if (! $isCollection || count($generic->genericTypes) !== 2) {
                return VisitorOperation::keep();
            }

            return VisitorOperation::replace(new TypeScriptArray([$generic->genericTypes[1]]));
        }, [TypeScriptGeneric::class]);

        return $unwrap->execute($type);
    }

    private function inlineValueObjects(TypeScriptNode $type): TypeScriptNode
    {
        $inline = Visitor::create()->before(function (TypeScriptReference $reference): VisitorOperation {
            $classString = $reference->reference instanceof ClassStringReference
                ? $reference->reference->classString
                : null;

            if ($classString === null || ! str_starts_with($classString, self::VALUE_OBJECT_NAMESPACE)) {
                return VisitorOperation::keep();
            }

            return VisitorOperation::replace($this->buildValueObject($classString));
        }, [TypeScriptReference::class]);

        return $inline->execute($type);
    }

    private function buildValueObject(string $classString): TypeScriptNode
    {
        $valueObject = PhpClassNode::fromClassString($classString);

        $properties = [];

        foreach ($valueObject->getMethod('__construct')?->getParameters() ?? [] as $parameter) {
            if (! $parameter->hasType() || ! $parameter->reflection->isPromoted()) {
                continue;
            }

            $type = $this->transpilePhpTypeNodeToTypeScriptTypeAction->execute(
                $parameter->getType(),
                $valueObject,
            );

            $properties[] = new TypeScriptProperty(
                Str::camel($parameter->getName()),
                $this->inlineValueObjects($this->unwrapCollections($type)),
            );
        }

        return new TypeScriptObject($properties);
    }
}
