<?php

declare(strict_types=1);

namespace Shared\Typescript;

use Spatie\TypeScriptTransformer\Attributes\NodeVisitable;
use Spatie\TypeScriptTransformer\Data\WritingContext;
use Spatie\TypeScriptTransformer\TypeScriptNodes\TypeScriptNode;

final class TypeScriptTuple implements TypeScriptNode
{
    /**
     * @param  TypeScriptNode[]  $types
     * @param  array<bool>  $optional  flags aligned by element index
     */
    public function __construct(
        #[NodeVisitable]
        public array $types,
        public array $optional = [],
    ) {}

    public function write(WritingContext $context): string
    {
        $types = implode(', ', array_map(
            fn (int $index, TypeScriptNode $type) => $type->write($context) . (($this->optional[$index] ?? false) ? '?' : ''),
            array_keys($this->types),
            $this->types,
        ));

        return "[$types]";
    }
}
