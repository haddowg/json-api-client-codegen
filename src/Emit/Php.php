<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

/**
 * Small, exact helpers for writing PHP source, so every emitter formats the same way.
 */
final class Php
{
    /**
     * A single-quoted string literal.
     */
    public static function string(string $value): string
    {
        return "'" . \str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
    }

    /**
     * A docblock from its lines, indented by `$indent` spaces. An empty string in `$lines` is a
     * blank line. With `$inline`, a single line renders on one line.
     *
     * @param list<string> $lines
     */
    public static function docblock(array $lines, int $indent = 0, bool $inline = true): string
    {
        $pad = \str_repeat(' ', $indent);

        if ($inline && \count($lines) === 1) {
            return $pad . '/** ' . $lines[0] . " */\n";
        }

        $out = $pad . "/**\n";
        foreach ($lines as $line) {
            $out .= $pad . ($line === '' ? ' *' : ' * ' . $line) . "\n";
        }

        return $out . $pad . " */\n";
    }

    /**
     * Wrap prose to the generated code's 100-column docblock width, given the indent it sits at.
     *
     * @return list<string>
     */
    public static function wrap(string $prose, int $indent = 0): array
    {
        $width = 100 - $indent - 3;
        $lines = [];

        foreach (\preg_split('/\R/', \trim($prose)) ?: [] as $paragraph) {
            $paragraph = \trim($paragraph);
            if ($paragraph === '') {
                $lines[] = '';
                continue;
            }
            foreach (\explode("\n", \wordwrap($paragraph, $width, "\n", false)) as $line) {
                $lines[] = $line;
            }
        }

        return $lines;
    }
}
