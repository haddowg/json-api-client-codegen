<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

/**
 * One generated declaration, as an emitter hands it to the {@see Assembler}.
 *
 * `$source` is the declaration alone: its docblock and body, with no `<?php`, namespace or `use`
 * lines, which the assembler writes once per file. `$imports` are fully-qualified names the
 * source refers to by short name. `$file` is the file the declaration belongs in, from
 * {@see FileKey}. `$primary` puts the type's main class (`Album`) first among its file's classes.
 */
final class ClassUnit
{
    /**
     * @param list<string> $imports fully-qualified class, interface, trait or enum names
     */
    public function __construct(
        public readonly string $name,
        public readonly UnitKind $kind,
        public readonly string $file,
        public readonly string $source,
        public readonly array $imports = [],
        public readonly bool $primary = false,
    ) {
        if (\preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) !== 1) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not a class name.', $name));
        }
    }
}
