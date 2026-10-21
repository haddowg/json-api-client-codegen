<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Writes;

/**
 * One member of a write input, with every type the generated code spells it as.
 *
 * `$native` and `$doc` are the value's type without `Missing` or `null`; `$doc` is what PHPStan
 * sees, and differs from `$native` where the native type cannot say enough (`array` against
 * `list<Identifier|Track>`). `$aliasDoc` is the same type written with the resource class's
 * shape alias where one exists, for classes that import it. `$input` is the type the array door
 * accepts, which is looser for dates and enums.
 */
final class WriteMember
{
    public function __construct(
        public readonly string $name,
        public readonly string $variable,
        public readonly string $setter,
        public readonly bool $relation,
        public readonly bool $required,
        public readonly bool $nullable,
        public readonly string $native,
        public readonly string $doc,
        public readonly string $aliasDoc,
        public readonly string $input,
        public readonly Coercion $coercion,
        public readonly ?string $enum,
        public readonly ?string $dateFormat,
        public readonly bool $toMany,
    ) {}

    /** The native type of a value that is always present: a required member, or a setter's parameter. */
    public function presentNative(): string
    {
        return self::union($this->native, false, $this->nullable, true);
    }

    /** The native type of a member that may be absent. */
    public function optionalNative(): string
    {
        return self::union($this->native, true, $this->nullable, false);
    }

    public function presentDoc(bool $alias): string
    {
        return self::union($alias ? $this->aliasDoc : $this->doc, false, $this->nullable, false);
    }

    public function optionalDoc(bool $alias): string
    {
        return self::union($alias ? $this->aliasDoc : $this->doc, true, $this->nullable, false);
    }

    /** Whether PHPStan needs a doc type to see what the native one cannot say. */
    public function needsDoc(bool $alias): bool
    {
        return ($alias ? $this->aliasDoc : $this->doc) !== $this->native;
    }

    private static function union(string $base, bool $missing, bool $nullable, bool $short): string
    {
        if ($base === 'mixed') {
            return 'mixed';
        }
        if (!$missing && $nullable && $short && !\str_contains($base, '|')) {
            return '?' . $base;
        }

        return $base . ($missing ? '|Missing' : '') . ($nullable ? '|null' : '');
    }
}
