<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

/**
 * The sentence a generated operation method's docblock gives the OAuth scopes the operation
 * needs, from a contract-16 document. It goes after the method's prose and before its tags, since
 * text after a tag reads as part of that tag.
 */
final class ScopeNote
{
    /**
     * `$doc` with the sentence and a blank line inserted before its first tag, or appended after a
     * blank line when it has none. Unchanged when the operation needs no scope.
     *
     * @param list<string>      $doc
     * @param list<string>|null $scopes
     *
     * @return list<string>
     */
    public static function insert(array $doc, ?array $scopes, int $indent = 4): array
    {
        if ($scopes === null || $scopes === []) {
            return $doc;
        }

        $note = Php::wrap(self::sentence($scopes), $indent);
        foreach ($doc as $index => $line) {
            if (\str_starts_with($line, '@')) {
                return [...\array_slice($doc, 0, $index), ...$note, '', ...\array_slice($doc, $index)];
            }
        }

        return [...$doc, '', ...$note];
    }

    /** @param non-empty-list<string> $scopes */
    public static function sentence(array $scopes): string
    {
        $named = \array_map(static fn(string $scope): string => '`' . $scope . '`', $scopes);
        $last = \array_pop($named);
        $list = $named === [] ? $last : \implode(', ', $named) . ' and ' . $last;

        return 'Needs an access token granting the ' . $list . ' scope' . ($named === [] ? '' : 's') . '.';
    }
}
