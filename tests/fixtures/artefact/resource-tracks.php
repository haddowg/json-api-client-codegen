<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Exceptions\FieldNotSelectedException;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\RelationNotIncludedException;
use haddowg\JsonApiClient\Resources\Relationship;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiClient\Support\Identifier;

/**
 * The `album` relation of a `tracks` resource, hydrated.
 *
 * This marker is what makes the depth rule visible. `$album->tracks[0]->album` compiles because
 * `Track` implements it, and throws at runtime unless `tracks.album` was included — narrowing
 * covers depth 1, the throw covers every depth.
 *
 * @generated from `components.schemas.TracksResource.properties.relationships.album`
 */
interface TrackHasAlbum
{
    /**
     * @throws \haddowg\JsonApiClient\Exceptions\RelationNotIncludedException when the read did not include `album` at this depth
     */
    public function album(): ?Album;

    public ?Album $album { get; }
}

/**
 * A `tracks` resource with no relation hydrated.
 *
 * Trimmed to what the `albums` surface reaches. The `playlists` relation keeps its linkage
 * companions but has no hydrated accessor here, because that would pull in the `playlists`
 * type; a full run emits both.
 *
 * `previewOffset` is a plain nullable string. The server's `H:i:s` wire form is not valid RFC 3339,
 * so the document declares no `format: time` for it, and there is nothing to coerce it to.
 *
 * @generated from `components.schemas.TracksResource`
 */
abstract class TrackBase extends ResourceObject
{
    /**
     * The resource type, which is a constant for a generated class and not a wire read.
     */
    public const string TYPE = 'tracks';

    public string $id { get => $this->id(); }

    public string $type { get => $this->type(); }

    /** @var non-empty-string */
    public string $title { get => $this->title(); }

    /** @var positive-int */
    public int $trackNumber { get => $this->trackNumber(); }

    public int $durationSeconds { get => $this->durationSeconds(); }

    public bool $explicit { get => $this->explicit(); }

    /** @var non-empty-list<non-empty-string> */
    public array $genres { get => $this->genres(); }

    public ?string $previewOffset { get => $this->previewOffset(); }

    public string $displayTitle { get => $this->displayTitle(); }

    public function id(): string
    {
        return $this->_identifier()->id;
    }

    /**
     * @return self::TYPE
     */
    public function type(): string
    {
        return self::TYPE;
    }

    /**
     * @return non-empty-string
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `title`
     */
    public function title(): string
    {
        /** @var non-empty-string $value */
        $value = $this->_string('title');

        return $value;
    }

    /**
     * @return positive-int
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `trackNumber`
     */
    public function trackNumber(): int
    {
        /** @var positive-int $value */
        $value = $this->_int('trackNumber');

        return $value;
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `durationSeconds`
     */
    public function durationSeconds(): int
    {
        return $this->_int('durationSeconds');
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `explicit`
     */
    public function explicit(): bool
    {
        return $this->_bool('explicit');
    }

    /**
     * No duplicate items.
     *
     * @return non-empty-list<non-empty-string>
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `genres`
     */
    public function genres(): array
    {
        /** @var non-empty-list<non-empty-string> $value */
        $value = $this->_stringList('genres');

        return $value;
    }

    /**
     * @throws FieldNotSelectedException when a sparse fieldset excluded `previewOffset`
     */
    public function previewOffset(): ?string
    {
        return $this->_nullableString('previewOffset');
    }

    /**
     * Read-only: present on the resource, absent from every write shape.
     *
     * @throws FieldNotSelectedException when a sparse fieldset excluded `displayTitle`
     */
    public function displayTitle(): string
    {
        return $this->_string('displayTitle');
    }

    /**
     * The `album` linkage, without hydrating anything. Never throws.
     */
    public function albumRef(): ?Identifier
    {
        return $this->_relationship('album', true)->identifier();
    }

    /**
     * Whether the `album` linkage points at anything. Never throws.
     */
    public function hasAlbum(): bool
    {
        return !$this->_relationship('album', true)->isEmpty();
    }

    /**
     * The `playlists` linkage, without hydrating anything. Never throws.
     *
     * @return list<Identifier>
     */
    public function playlistsRef(): array
    {
        return $this->_relationship('playlists', false)->identifiers();
    }

    /**
     * Whether the `playlists` linkage points at anything. Never throws.
     */
    public function hasPlaylists(): bool
    {
        return !$this->_relationship('playlists', false)->isEmpty();
    }

    /**
     * A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind.
     * This is the door to a relation that carries no value.
     *
     * @param 'album'|'playlists' $name
     */
    public function _rel(string $name): Relationship
    {
        return $this->_relationship($name, $name === 'album');
    }
}

/**
 * A `tracks` resource, trimmed to what the `albums` surface reaches.
 *
 * @generated from `components.schemas.TracksResource`
 */
final class Track extends TrackBase implements TrackHasAlbum
{
    public ?Album $album { get => $this->album(); }

    /**
     * @param array<string, mixed> $raw
     *
     * @throws MalformedDocument when the resource object is not a `tracks` one
     */
    public static function _hydrate(array $raw, ResourceContext $context): self
    {
        $identifier = Identifier::fromArray($raw, 'a "' . self::TYPE . '" resource object');

        if ($identifier->type !== self::TYPE) {
            throw MalformedDocument::unexpectedMember(
                'The resource object',
                'type',
                '"' . self::TYPE . '" (got "' . $identifier->type . '")',
            );
        }

        return new self($identifier, $raw, $context);
    }

    /**
     * @param array<string, mixed> $meta
     */
    public static function ref(string $id, array $meta = []): Identifier
    {
        return Identifier::of(self::TYPE, $id, $meta);
    }

    /**
     * @throws RelationNotIncludedException when the read did not include `album` at this depth
     */
    public function album(): ?Album
    {
        $raw = $this->_includedOne('album', 'albumRef');

        return $raw === null ? null : Album::_hydrate($raw, $this->_child('album'));
    }
}
