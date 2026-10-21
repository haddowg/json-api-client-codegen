<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

/**
 * Groups emitted declarations into their files and writes the class map that loads them.
 *
 * Output is deterministic: declarations are ordered by {@see UnitKind}, then the type's primary
 * class, then name, and imports are de-duplicated and sorted the way PHP-CS-Fixer's
 * `ordered_imports` expects, so a regeneration diff holds only what changed.
 */
final class Assembler
{
    /**
     * @param iterable<ClassUnit|Contribution> $emitted
     *
     * @return array<string, string> file contents keyed by path relative to the output directory
     */
    public static function assemble(iterable $emitted, GeneratorConfig $config, string $specHash): array
    {
        $units = [];
        $contributions = [];
        foreach ($emitted as $item) {
            if ($item instanceof Contribution) {
                $contributions[] = $item;
            } else {
                $units[] = $item;
            }
        }
        $units = self::merge($units, $contributions);

        $byFile = [];
        $seen = [];

        foreach ($units as $unit) {
            $key = \strtolower($unit->name);
            if (isset($seen[$key])) {
                throw new \LogicException(\sprintf('Two generated declarations are named "%s" and "%s" (in %s and %s); PHP class names are case-insensitive.', $seen[$key]->name, $unit->name, $seen[$key]->file, $unit->file));
            }
            $seen[$key] = $unit;
            $byFile[$unit->file][] = $unit;
        }

        $files = [];
        $map = [];

        foreach ($byFile as $file => $fileUnits) {
            self::assertFileName($file, \array_map(static fn(ClassUnit $unit): string => $unit->name, \array_values($seen)));
            \usort($fileUnits, static fn(ClassUnit $a, ClassUnit $b): int => [$a->kind->value, $a->primary ? 0 : 1, $a->name] <=> [$b->kind->value, $b->primary ? 0 : 1, $b->name]);
            $files[$file] = self::file($fileUnits, $config);
            foreach ($fileUnits as $unit) {
                $map[$unit->name] = $file;
            }
        }

        \ksort($files, \SORT_STRING);
        \ksort($map, \SORT_STRING);
        $files[FileKey::AUTOLOAD] = self::autoload($map, $config, $specHash);

        return $files;
    }

    /**
     * Fold every contribution into the declaration it targets, in contribution order.
     *
     * @param list<ClassUnit>    $units
     * @param list<Contribution> $contributions
     *
     * @return list<ClassUnit>
     */
    private static function merge(array $units, array $contributions): array
    {
        $byTarget = [];
        foreach ($contributions as $index => $contribution) {
            $byTarget[$contribution->target][] = ['order' => $contribution->order, 'index' => $index, 'contribution' => $contribution];
        }

        $known = \array_map(static fn(ClassUnit $unit): string => $unit->name, $units);
        foreach (\array_keys($byTarget) as $target) {
            if (!\in_array((string) $target, $known, true)) {
                throw new \LogicException(\sprintf('A contribution targets "%s", which no emitter generates.', $target));
            }
        }

        $out = [];
        foreach ($units as $unit) {
            $own = $byTarget[$unit->name] ?? [];
            if ($own === []) {
                $out[] = $unit;
                continue;
            }
            \usort($own, static fn(array $a, array $b): int => [$a['order'], $a['index']] <=> [$b['order'], $b['index']]);

            $tags = [];
            $members = [];
            $imports = $unit->imports;
            foreach ($own as $entry) {
                $contribution = $entry['contribution'];
                \array_push($tags, ...$contribution->tags);
                if (\trim($contribution->members) !== '') {
                    $members[] = \rtrim($contribution->members) . "\n";
                }
                \array_push($imports, ...$contribution->imports);
            }

            $out[] = new ClassUnit(
                $unit->name,
                $unit->kind,
                $unit->file,
                self::withMembers(self::withTags($unit, $tags), $members),
                $imports,
                $unit->primary,
            );
        }

        return $out;
    }

    /**
     * Insert tag lines into the owner's docblock, above its `@generated` line, with a blank line
     * either side of the block.
     *
     * @param list<string> $tags
     */
    private static function withTags(ClassUnit $unit, array $tags): string
    {
        if ($tags === []) {
            return $unit->source;
        }

        $marker = \strpos($unit->source, "\n * @generated");
        if ($marker === false) {
            throw new \LogicException(\sprintf('%s receives contributed tags but its docblock has no @generated line to place them above.', $unit->name));
        }

        $before = \substr($unit->source, 0, $marker);
        $separator = \str_ends_with($before, "\n *") || \str_ends_with($before, '/**') ? '' : "\n *";
        $block = \implode('', \array_map(static fn(string $tag): string => "\n * " . $tag, $tags)) . "\n *";

        return $before . $separator . $block . \substr($unit->source, $marker);
    }

    /**
     * Insert contributed members at the top of the owner's body, a blank line between each and
     * before whatever the owner declares itself.
     *
     * @param list<string> $members
     */
    private static function withMembers(string $source, array $members): string
    {
        if ($members === []) {
            return $source;
        }

        $open = \strpos($source, "\n{\n");
        if ($open === false) {
            throw new \LogicException('A declaration receiving contributed members has no body opening on its own line.');
        }
        $open += 3;
        $rest = \substr($source, $open);

        return \substr($source, 0, $open) . \implode("\n", $members) . (\str_starts_with($rest, '}') ? '' : "\n") . $rest;
    }

    /**
     * @param list<ClassUnit> $units
     */
    private static function file(array $units, GeneratorConfig $config): string
    {
        $imports = [];
        foreach ($units as $unit) {
            foreach ($unit->imports as $import) {
                $import = \ltrim($import, '\\');
                if (self::namespaceOf($import) !== $config->namespace) {
                    $imports[$import] = true;
                }
            }
        }
        $imports = \array_keys($imports);
        \usort($imports, static fn(string $a, string $b): int => \strcasecmp($a, $b));

        $out = "<?php\n\ndeclare(strict_types=1);\n\nnamespace " . $config->namespace . ";\n\n";
        if ($imports !== []) {
            $out .= \implode('', \array_map(static fn(string $import): string => 'use ' . $import . ";\n", $imports)) . "\n";
        }

        return $out . \implode("\n", \array_map(static fn(ClassUnit $unit): string => \rtrim($unit->source) . "\n", $units));
    }

    /**
     * @param array<string, string> $map short class name => file
     */
    private static function autoload(array $map, GeneratorConfig $config, string $specHash): string
    {
        $prefix = \str_replace('\\', '\\\\', $config->namespace) . '\\\\';
        $entries = '';
        foreach ($map as $class => $file) {
            $entries .= "        '" . $prefix . $class . "' => '" . $file . "',\n";
        }

        return "<?php\n\ndeclare(strict_types=1);\n\n"
            . "/*\n"
            . " * Loads the generated " . $config->clientName . " client. Classes share a file, one file per resource type,\n"
            . " * so this map is how each one is found. Register it once through composer.json's\n"
            . " * `autoload.files`; regenerating rewrites it, so a new type never needs a `dump-autoload`.\n"
            . " *\n"
            . " * @generated from an OpenAPI document with sha256 " . $specHash . "\n"
            . " */\n\n"
            . "\\spl_autoload_register(static function (string \$class): void {\n"
            . "    static \$map = [\n" . $entries . "    ];\n\n"
            . "    if (isset(\$map[\$class])) {\n"
            . "        require_once __DIR__ . '/' . \$map[\$class];\n"
            . "    }\n"
            . "});\n";
    }

    /**
     * @param list<string> $classNames
     */
    private static function assertFileName(string $file, array $classNames): void
    {
        $stem = \strtolower(\basename($file, '.php'));
        foreach ($classNames as $class) {
            if (\strtolower($class) === $stem) {
                throw new \LogicException(\sprintf('Generated file "%s" would collide with class "%s" on a case-insensitive filesystem.', $file, $class));
            }
        }
    }

    private static function namespaceOf(string $class): string
    {
        $at = \strrpos($class, '\\');

        return $at === false ? '' : \substr($class, 0, $at);
    }
}
