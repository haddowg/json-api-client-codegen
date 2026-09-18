<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * One attribute a write accepts, in the create or the update document.
 *
 * The value members mean what they do on {@see AttributeDescriptor}. `$required` is the one
 * addition, and it is what makes omitting a required member a static error on the generated DTO
 * rather than a 422 from the server: a required member takes no default, so leaving it out is an
 * `ArgumentCountError` and a PHPStan `Missing parameter` at once.
 *
 * The create and update sets are read separately rather than derived from the read attributes
 * with a writable flag, because they genuinely diverge — `artists.createdAt` is accepted on
 * create and not on update, and `catalog-exports` accepts one attribute on create and none on
 * update. A read-only attribute is simply absent from both, never present and marked.
 */
final class WriteAttributeDescriptor
{
    public function __construct(
        public readonly string $name,
        public readonly string $format,
        public readonly ?string $enum,
        public readonly bool $nullable,
        public readonly ?ValueSchema $schema,
        public readonly bool $required,
    ) {}
}
