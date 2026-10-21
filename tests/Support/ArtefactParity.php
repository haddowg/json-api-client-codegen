<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Support;

use PHPUnit\Framework\Assert;

/**
 * Compares one generated declaration against the hand-written reference artefact.
 *
 * The artefact (`tests/fixtures/artefact`, a snapshot of `json-api-client/tests/Generated`) is
 * the spec for the generated code, but much of its docblock prose explains the design to a human
 * reader and is not derivable from the document. So parity is checked on what can be derived:
 * every code token, plus the type-bearing PHPDoc tags (`@param`, `@return`, `@var`, `@throws`,
 * `@template`, `@phpstan-*`, `@use`, `@extends`, `@implements`). Free-text prose, plain comments
 * and whitespace are ignored. An emitter's full output, prose included, is pinned separately by
 * its golden files.
 */
final class ArtefactParity
{
    public const string DIRECTORY = __DIR__ . '/../fixtures/artefact';

    private const array KEPT_TAGS = ['@param', '@return', '@var', '@throws', '@template', '@template-covariant', '@use', '@extends', '@implements'];

    public static function assertMatches(string $declaration, string $artefactFile, string $generatedSource): void
    {
        self::assertSourceMatches($declaration, self::artefact($artefactFile), $generatedSource);
    }

    /**
     * The same comparison against artefact source the caller has prepared, for a declaration
     * only part of which one emitter generates.
     */
    public static function assertSourceMatches(string $declaration, string $artefact, string $generatedSource): void
    {
        Assert::assertSame(
            self::readable(self::normalised($artefact, $declaration)),
            self::readable(self::normalised($generatedSource, $declaration)),
            \sprintf('%s differs from the artefact in code or type-bearing PHPDoc.', $declaration),
        );
    }

    public static function artefact(string $file): string
    {
        $artefact = \file_get_contents(self::DIRECTORY . '/' . $file);
        Assert::assertIsString($artefact, 'Missing artefact file ' . $file);

        return $artefact;
    }

    /**
     * The normalised token stream of one declaration, as one line.
     */
    public static function normalised(string $source, string $declaration): string
    {
        $tokens = \array_values(\PhpToken::tokenize($source));
        [$start, $end] = self::bounds($tokens, $declaration);

        $out = [];
        for ($i = $start; $i <= $end; $i++) {
            $token = $tokens[$i];
            if ($token->is(\T_DOC_COMMENT)) {
                foreach (self::tags($token->text) as $tag) {
                    $out[] = $tag;
                }
            } elseif ($token->is([\T_COMMENT, \T_WHITESPACE])) {
                continue;
            } else {
                $out[] = $token->text;
            }
        }

        return \implode(' ', $out);
    }

    /**
     * @param list<\PhpToken> $tokens
     *
     * @return array{int, int} first and last token index of the declaration, docblock included
     */
    private static function bounds(array $tokens, string $declaration): array
    {
        $count = \count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if (!$tokens[$i]->is([\T_CLASS, \T_INTERFACE, \T_TRAIT, \T_ENUM])) {
                continue;
            }
            $name = self::next($tokens, $i);
            if ($name === null || $tokens[$name]->text !== $declaration) {
                continue;
            }

            $start = $i;
            for ($j = $i - 1; $j >= 0; $j--) {
                if ($tokens[$j]->is([\T_WHITESPACE, \T_FINAL, \T_ABSTRACT, \T_READONLY])) {
                    if (!$tokens[$j]->is(\T_WHITESPACE)) {
                        $start = $j;
                    }
                    continue;
                }
                if ($tokens[$j]->is(\T_DOC_COMMENT)) {
                    $start = $j;
                }
                break;
            }

            $depth = 0;
            for ($k = $name; $k < $count; $k++) {
                if ($tokens[$k]->text === '{' || $tokens[$k]->is([\T_CURLY_OPEN, \T_DOLLAR_OPEN_CURLY_BRACES])) {
                    $depth++;
                } elseif ($tokens[$k]->text === '}') {
                    $depth--;
                    if ($depth === 0) {
                        return [$start, $k];
                    }
                }
            }
        }

        throw new \RuntimeException(\sprintf('No declaration named "%s" in the given source.', $declaration));
    }

    /**
     * @param list<\PhpToken> $tokens
     */
    private static function next(array $tokens, int $from): ?int
    {
        for ($i = $from + 1, $count = \count($tokens); $i < $count; $i++) {
            if (!$tokens[$i]->is(\T_WHITESPACE)) {
                return $tokens[$i]->is(\T_STRING) ? $i : null;
            }
        }

        return null;
    }

    /**
     * The type-bearing tags of a docblock, each reduced to its tag and type (and, for `@param`,
     * the parameter name), with whitespace collapsed.
     *
     * @return list<string>
     */
    private static function tags(string $docblock): array
    {
        $tags = [];

        foreach (\preg_split('/\R/', $docblock) ?: [] as $line) {
            $line = \trim((string) \preg_replace('#^\s*(/\*\*|\*/|\*)?#', '', $line));
            $line = \trim((string) \preg_replace('#\*/$#', '', $line));
            if (!\str_starts_with($line, '@')) {
                continue;
            }

            $line = (string) \preg_replace('/\s+/', ' ', $line);
            $tag = \explode(' ', $line, 2)[0];

            if ($tag === '@param' && \preg_match('/^(@param .*?\$\w+)/', $line, $m) === 1) {
                $tags[] = $m[1];
            } elseif ($tag === '@throws') {
                $tags[] = \implode(' ', \array_slice(\explode(' ', $line), 0, 2));
            } elseif (\in_array($tag, self::KEPT_TAGS, true) || \str_starts_with($tag, '@phpstan-')) {
                $tags[] = $line;
            }
        }

        return $tags;
    }

    /** One token group per line, so a failing comparison prints a readable diff. */
    private static function readable(string $normalised): string
    {
        return (string) \preg_replace('/ ([;{}]) /', " $1\n", $normalised);
    }
}
