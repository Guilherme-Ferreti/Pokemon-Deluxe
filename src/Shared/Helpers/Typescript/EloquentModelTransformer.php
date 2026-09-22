<?php

declare(strict_types=1);

namespace Shared\Helpers\Typescript;

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
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptObject;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptProperty;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptReference;
use Spatie\TypeScriptTransformer\Visitor\Visitor;
use Spatie\TypeScriptTransformer\Visitor\VisitorOperation;

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

        foreach ($parsedClass->properties ?? [] as $name => $annotation) {
            $type = $this->transpilePhpStanTypeToTypeScriptTypeAction->execute(
                $annotation->type,
                $phpClassNode,
                $parsedClass->templates ?? [],
            );

            $properties[] = new TypeScriptProperty(
                Str::camel($name),
                $this->inlineValueObjects($this->unwrapCollections($type)),
            );
        }

        return new TypeScriptObject($properties);
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
