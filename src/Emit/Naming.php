<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

use haddowg\JsonApiCodegen\Spec\SpecException;

/**
 * Every generated name, in one place, so emitters built separately agree on what to call things.
 *
 * A type's classes are named from its singular (`albums` gives `Album`), except the type
 * accessor, which keeps the plural (`Albums`). Relations, filters and members are PascalCased
 * from their JSON:API names, dropping the separators PHP identifiers cannot carry.
 */
final class Naming
{
    /** `albums` → `Album`: the concrete resource class, and the prefix of every per-type class. */
    public static function resource(string $type): string
    {
        return self::className($type, self::pascal(self::singular($type)));
    }

    /** `albums` → `Albums`: the type accessor on the client. */
    public static function accessor(string $type): string
    {
        return self::className($type, self::pascal($type));
    }

    /** `albums` → `AlbumBase`. */
    public static function base(string $type): string
    {
        return self::resource($type) . 'Base';
    }

    /** `albums`, `artist` → `AlbumHasArtist`: the relation marker interface. */
    public static function marker(string $type, string $relation): string
    {
        return self::resource($type) . 'Has' . self::pascal($relation);
    }

    /** `albums`, `artist` → `AlbumArtistRelation`: the relationship handle. */
    public static function relationHandle(string $type, string $relation): string
    {
        return self::resource($type) . self::pascal($relation) . 'Relation';
    }

    /** `albums`, `Query` → `AlbumQuery`; likewise `Projection`, `Projecting`, `Handle`, `Create`, `CreateBuilder`, `Update`, `Actions`, `CollectionActions`. */
    public static function perType(string $type, string $suffix): string
    {
        return self::resource($type) . $suffix;
    }

    /** `albums`, `FilterShape` → `AlbumFilterShape`: a `@phpstan-type` alias declared on the resource class. */
    public static function alias(string $type, string $suffix): string
    {
        return self::resource($type) . $suffix;
    }

    /** `MusicCatalog` → `MusicCatalogClient`. */
    public static function client(string $clientName): string
    {
        return $clientName . 'Client';
    }

    /** `artist.name` → `whereArtistName`. */
    public static function filterMethod(string $filter): string
    {
        return 'where' . self::pascal($filter);
    }

    /** `artist` → `withArtist`. */
    public static function includeMethod(string $relation): string
    {
        return 'with' . self::pascal($relation);
    }

    /** A JSON:API member as a PHP method or property name: `release-info` → `releaseInfo`. */
    public static function member(string $name): string
    {
        $member = self::camel($name);
        if (!Identifier::isMemberName($member)) {
            throw SpecException::unusableName(\sprintf('the member %s', \json_encode($name, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_INVALID_UTF8_SUBSTITUTE)), $member, 'method or property name');
        }

        return $member;
    }

    /**
     * `release-info` → `releaseInfo`, unchecked, for a caller that makes its own identifier of
     * the result, such as a local variable that takes a prefix when it would start with a digit.
     */
    public static function camel(string $name): string
    {
        return \lcfirst(self::pascal($name));
    }

    /**
     * Every per-type class name starts with a type's resource or accessor name, so checking those
     * two checks them all: what follows is letters and digits.
     */
    private static function className(string $type, string $class): string
    {
        if (!Identifier::isClassName($class)) {
            throw SpecException::unusableName(\sprintf('the type %s', \json_encode($type, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_INVALID_UTF8_SUBSTITUTE)), $class, 'class name');
        }

        return $class;
    }

    /** Separators become word breaks: `artist.name`, `artist_name` and `artist-name` all give `ArtistName`. */
    public static function pascal(string $name): string
    {
        $words = \preg_split('/[^A-Za-z0-9]+/', $name, -1, \PREG_SPLIT_NO_EMPTY) ?: [];

        return \implode('', \array_map(static fn(string $word): string => \ucfirst($word), $words));
    }

    /**
     * The singular of a plural JSON:API type, by the English rules type names actually use:
     * `countries` → `country`, `statuses` → `status`, `releases` → `release`. Only the last
     * hyphenated segment is inflected (`catalog-exports` → `catalog-export`).
     */
    public static function singular(string $type): string
    {
        $at = \strrpos($type, '-');
        $head = $at === false ? '' : \substr($type, 0, $at + 1);
        $word = $at === false ? $type : \substr($type, $at + 1);

        return $head . match (true) {
            \preg_match('/[^aeiou]ies$/', $word) === 1 => \substr($word, 0, -3) . 'y',
            \preg_match('/(ss|x|z|ch|sh|us)es$/', $word) === 1 => \substr($word, 0, -2),
            \str_ends_with($word, 'ss'), \str_ends_with($word, 'us') => $word,
            \str_ends_with($word, 's') => \substr($word, 0, -1),
            default => $word,
        };
    }
}
