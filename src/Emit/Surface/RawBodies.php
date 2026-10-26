<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Surface;

use haddowg\JsonApiCodegen\Descriptor\ActionDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\RawMediaDescriptor;
use haddowg\JsonApiCodegen\Descriptor\RawMediaKind;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\Identifier;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\ReadModel\ReadModelEmitter;
use haddowg\JsonApiCodegen\Emit\ReadModel\Shape;
use haddowg\JsonApiCodegen\Emit\ReadModel\ShapeKind;

/**
 * The value classes raw JSON response bodies are read into.
 *
 * A JSON body whose schema is an object with members becomes one class. A schema component is
 * one class however many actions answer with it, named after the component and declared in the
 * file of the first type that uses it; an inline schema is named after its action and media type.
 * Any other JSON body has no class and is returned decoded.
 */
final class RawBodies
{
    /** Per-type class name suffixes, which a component name must not take over. */
    private const array TYPE_SUFFIXES = ['', 'Base', 'Query', 'Projection', 'Projecting', 'Handle', 'Create', 'CreateBuilder', 'Update', 'Actions', 'CollectionActions'];

    /**
     * Every body class the API needs, keyed by {@see key()}.
     *
     * @return array<string, array{class: string, shape: Shape, file: string, from: string}>
     */
    public static function of(ApiDescriptor $api): array
    {
        $taken = \array_fill_keys(\array_map(\strtolower(...), \array_keys($api->enums)), true);
        foreach ($api->resources as $type => $resource) {
            foreach (self::TYPE_SUFFIXES as $suffix) {
                $taken[\strtolower(Naming::perType($type, $suffix))] = true;
            }
            $taken[\strtolower(Naming::accessor($type))] = true;
        }

        $out = [];
        foreach ($api->resources as $type => $resource) {
            foreach ($resource->actions as $action) {
                foreach ($action->responses as $media) {
                    $shape = self::shape($media);
                    $key = self::key($type, $action, $media);
                    if ($shape === null || isset($out[$key])) {
                        continue;
                    }

                    $class = $media->component !== null && Identifier::isClassName($media->component) && !isset($taken[\strtolower($media->component)])
                        ? $media->component
                        : Naming::perType($type, Naming::pascal($action->name) . Naming::pascal(self::subtype($media->mediaType)) . 'Body');
                    $taken[\strtolower($class)] = true;
                    $out[$key] = [
                        'class' => $class,
                        'shape' => $shape,
                        'file' => FileKey::resource($type),
                        'from' => $media->component !== null
                            ? '`components.schemas.' . $media->component . '`'
                            : 'the `' . $media->mediaType . '` response of `' . $action->method . ' ' . $action->path . '`',
                    ];
                }
            }
        }

        return $out;
    }

    /** The class a raw response body is read into, or null when it is returned as it is. */
    public static function className(ApiDescriptor $api, string $type, ActionDescriptor $action, RawMediaDescriptor $media): ?string
    {
        return self::of($api)[self::key($type, $action, $media)]['class'] ?? null;
    }

    /**
     * @return list<ClassUnit>
     */
    public static function units(ApiDescriptor $api): array
    {
        $units = [];
        foreach (self::of($api) as $body) {
            $doc = [
                ...Php::wrap('A raw JSON response body, narrowed member by member: an optional member of the wrong type is dropped rather than trusted, and a required one throws.'),
                '',
                '@generated from ' . $body['from'],
            ];
            $units[] = ReadModelEmitter::valueClass($body['class'], 'The ' . $body['class'] . ' body', $body['shape']->members, $doc, $body['file']);
        }

        return $units;
    }

    private static function key(string $type, ActionDescriptor $action, RawMediaDescriptor $media): string
    {
        return $media->component ?? $type . ' ' . $action->name . ' ' . $media->mediaType;
    }

    private static function shape(RawMediaDescriptor $media): ?Shape
    {
        if ($media->kind !== RawMediaKind::Json || $media->schema === null) {
            return null;
        }
        $shape = Shape::of($media->schema)->merged();

        return $shape->kind === ShapeKind::Object && $shape->members !== [] && !$shape->nullable ? $shape : null;
    }

    /** `application/vnd.acme.report+json` → `vnd.acme.report+json`. */
    private static function subtype(string $mediaType): string
    {
        $essence = \trim(\explode(';', $mediaType, 2)[0]);
        $at = \strpos($essence, '/');

        return $at === false ? $essence : \substr($essence, $at + 1);
    }
}
