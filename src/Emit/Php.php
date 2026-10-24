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
            return $pad . self::comment($lines[0]) . "\n";
        }

        $out = $pad . "/**\n";
        foreach ($lines as $line) {
            $out .= $pad . ($line === '' ? ' *' : ' * ' . self::docText($line)) . "\n";
        }

        return $out . $pad . " */\n";
    }

    /** A one-line docblock, such as `/** @var T *\/` ahead of a statement. */
    public static function comment(string $text): string
    {
        return '/** ' . self::docText($text) . ' */';
    }

    /**
     * Text made safe to sit on one line of a docblock: `*\/` is broken up so it cannot close the
     * comment, and a line break or other control character becomes a space so the text cannot
     * start a line of its own and be read as a tag.
     */
    public static function docText(string $text): string
    {
        // Byte-wise when the text is not UTF-8, which still catches every ASCII line break.
        $text = \preg_replace('/\R|[\x00-\x1F\x7F]/u', ' ', $text) ?? (string) \preg_replace('/[\x00-\x1F\x7F]/', ' ', $text);

        return \str_replace('*/', '*\\/', $text);
    }

    /**
     * A string literal type for a docblock, such as a member of `'a'|'b'`, that PHPStan reads back
     * as exactly `$value`. Plain printable text is single-quoted. Anything that would close the
     * comment or break the line is double-quoted with escapes, which PHPStan decodes.
     */
    public static function docString(string $value): string
    {
        if (\preg_match('/^[\x20-\x7E]*$/D', $value) === 1 && !\str_contains($value, '*/')) {
            return "'" . \addcslashes($value, "'\\") . "'";
        }

        $escaped = (string) \preg_replace_callback(
            '/[\x00-\x1F\x7F"\\\\$]|(?<=\*)\//',
            static fn(array $match): string => match ($match[0]) {
                '"', '\\', '$' => '\\' . $match[0],
                default => \sprintf('\\x%02X', \ord($match[0])),
            },
            $value,
        );

        return '"' . $escaped . '"';
    }

    /** An array-shape key for a docblock: bare where it is an identifier, a string literal otherwise. */
    public static function docKey(string $name): string
    {
        return Identifier::isValid($name) ? $name : self::docString($name);
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

        // Prose can come from the document, and a `*/` in it would close the docblock early.
        $prose = \str_replace('*/', '*\\/', $prose);

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
