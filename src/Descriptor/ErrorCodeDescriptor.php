<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One code from the document's error catalogue: a `const` branch of the `code` of the shape
 * component it is grouped into (`Error403InsufficientScope`), which pins its `status`, may
 * require one `source` member and may declare a closed `meta`. The branch carries the code's
 * title and description; its example `detail` comes from its `components.examples` entry.
 *
 * `$class` is the exception class generated for it (`AccessDeniedError`). `$operations` lists the
 * operations (`PATCH /articles/{id}`) that raise a code scoped to them, and is empty for a code
 * every operation can raise.
 *
 * The catalogue is open. An error carrying a code not listed here is still a valid error, so
 * nothing generated from these may assume the list is complete.
 */
final class ErrorCodeDescriptor
{
    /**
     * @param list<string> $operations sorted
     */
    public function __construct(
        public readonly string $code,
        public readonly int $status,
        public readonly string $schema,
        public readonly string $class,
        public readonly string $title,
        public readonly ?ErrorSourceKind $source,
        public readonly ?string $description = null,
        public readonly ?string $exampleDetail = null,
        public readonly ?ValueSchema $meta = null,
        public readonly bool $metaRequired = false,
        public readonly array $operations = [],
    ) {}

    /**
     * A code in PascalCase, word by word: `INSUFFICIENT_SCOPE` → `InsufficientScope`. A word in
     * capitals is lowercased first, any other keeps its own case.
     */
    public static function pascal(string $code): string
    {
        $words = \preg_split('/[^A-Za-z0-9]+/', $code, -1, \PREG_SPLIT_NO_EMPTY) ?: [];

        return \implode('', \array_map(
            static fn(string $word): string => \ucfirst(\strtoupper($word) === $word ? \strtolower($word) : $word),
            $words,
        ));
    }

    /** The class a code's exception is generated as: its PascalCase name ending in `Error` once. */
    public static function className(string $code): string
    {
        $pascal = self::pascal($code);

        return \str_ends_with($pascal, 'Error') ? $pascal : $pascal . 'Error';
    }

    /** The class a code's declared `meta` is generated as: `InsufficientScopeMeta`. */
    public function metaClass(): string
    {
        return self::pascal($this->code) . 'Meta';
    }
}
