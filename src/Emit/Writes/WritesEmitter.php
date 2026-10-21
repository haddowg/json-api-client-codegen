<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Writes;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ClientIdPolicy;
use haddowg\JsonApiCodegen\Descriptor\EnumCase;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Contribution;
use haddowg\JsonApiCodegen\Emit\Emitter;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\UnitKind;

/**
 * The write inputs of every type that can be created or updated: the create DTO and its
 * builder, the update DTO (which is its own fluent form), and the input aliases and descriptor
 * constants they share, contributed to the resource class.
 *
 * Each class exists only where its operation does. A type with no create has no `AlbumCreate`,
 * and a create that forbids client ids has no id parameter.
 */
final class WritesEmitter implements Emitter
{
    public const int ORDER = 20;

    public function emit(ApiDescriptor $api, GeneratorConfig $config): iterable
    {
        foreach ($api->resources as $resource) {
            $model = WriteModel::of($api, $resource);
            if (!$model->writes()) {
                continue;
            }

            if ($model->create !== null) {
                yield self::unit(Naming::perType($resource->type, 'Create'), $resource->type, CreateSource::dto($model, $model->create));
                yield self::unit(Naming::perType($resource->type, 'CreateBuilder'), $resource->type, CreateSource::builder($model, $model->create));
            }
            if ($model->update !== null) {
                yield self::unit(Naming::perType($resource->type, 'Update'), $resource->type, UpdateSource::dto($model, $model->update));
            }

            yield self::contribution($api, $model);
        }
    }

    private static function unit(string $name, string $type, string $source): ClassUnit
    {
        return new ClassUnit($name, UnitKind::FinalClass, FileKey::resource($type), $source, WriteSource::imports($source));
    }

    private static function contribution(ApiDescriptor $api, WriteModel $model): Contribution
    {
        $resource = $model->resource;
        $type = $resource->type;
        $members = [...$model->create ?? [], ...$model->update ?? []];

        $tags = [];
        $enums = [];
        $dates = false;
        $relations = [];
        foreach ($members as $member) {
            if ($member->enum !== null && !isset($enums[$member->name])) {
                $values = \array_map(static fn(EnumCase $case): string => Php::string($case->value), $api->enum($member->enum)->cases ?? []);
                \sort($values, \SORT_STRING);
                $enums[$member->name] = '@phpstan-type ' . Naming::alias($type, Naming::pascal($member->name) . 'Input') . ' ' . \implode('|', [$member->enum, ...$values]);
            }
            $dates = $dates || $member->dateFormat !== null;
            if ($member->relation && !isset($relations[$member->name])) {
                $relations[$member->name] = '@phpstan-type ' . $member->input . ' ' . $member->doc . ($member->nullable ? '|null' : '');
            }
        }
        \array_push($tags, ...\array_values($enums));
        if ($dates) {
            $tags[] = '@phpstan-type ' . Naming::alias($type, 'DateInput') . ' \DateTimeImmutable|string';
        }
        \array_push($tags, ...\array_values($relations));
        if ($model->create !== null) {
            $tags[] = '@phpstan-type ' . Naming::alias($type, 'CreateShape') . ' ' . self::shape($model, $model->create, true);
        }
        if ($model->update !== null) {
            $tags[] = '@phpstan-type ' . Naming::alias($type, 'UpdateShape') . ' ' . self::shape($model, $model->update, false);
        }

        $constants = [self::constant(
            ['Every member a write input accepts, in document order. The runtime guard on the array door checks against this, and the did-you-mean is drawn from it.'],
            'WRITABLE',
            $model->writable(),
        )];
        foreach (['create' => $model->create, 'update' => $model->update] as $operation => $operationMembers) {
            if ($operationMembers !== null && self::guardConstant($model, $operationMembers, $operation) !== 'WRITABLE') {
                $constants[] = self::constant(
                    ['The members the ' . $operation . ' document accepts, where they differ from `WRITABLE`.'],
                    self::guardConstant($model, $operationMembers, $operation),
                    $resource->inDocumentOrder(WriteModel::names($operationMembers)),
                );
            }
        }
        if ($model->create !== null) {
            $readOnly = $resource->inDocumentOrder($resource->readOnlyAttributes());
            $constants[] = self::constant(
                ['Members a create document requires.' . ($readOnly === [] ? '' : ' Read-only attributes (' . \implode(', ', \array_map(static fn(string $name): string => '`' . $name . '`', $readOnly)) . ') are in no write shape.')],
                'REQUIRED_ON_CREATE',
                $model->requiredOnCreate(),
            );
        }

        return new Contribution(
            Naming::resource($type),
            self::ORDER,
            $tags,
            \implode("\n", $constants),
            $relations === [] ? [] : [WriteSource::IMPORTS['Identifier']],
        );
    }

    /**
     * The constant `from()` validates the keys of one operation's array against: `WRITABLE`
     * when the operation accepts every writable member, else its own list.
     *
     * @param list<WriteMember> $members
     */
    public static function guardConstant(WriteModel $model, array $members, string $operation): string
    {
        return $model->resource->inDocumentOrder(WriteModel::names($members)) === $model->writable()
            ? 'WRITABLE'
            : 'WRITABLE_ON_' . \strtoupper($operation);
    }

    /**
     * @param list<WriteMember> $members
     */
    private static function shape(WriteModel $model, array $members, bool $create): string
    {
        $byName = [];
        foreach ($members as $member) {
            $byName[$member->name] = $member;
        }

        $entries = [];
        if ($create && $model->clientId() !== ClientIdPolicy::Forbidden) {
            $entries[] = $model->clientId() === ClientIdPolicy::Required ? 'id: string' : 'id?: string|null';
        }
        foreach ($model->resource->inDocumentOrder(\array_keys($byName)) as $name) {
            $member = $byName[$name];
            $key = \preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) === 1 ? $name : Php::string($name);
            $entries[] = $key . ($member->required ? '' : '?') . ': ' . $member->input;
        }

        return 'array{' . \implode(', ', $entries) . '}';
    }

    /**
     * @param list<string> $prose
     * @param list<string> $values
     */
    private static function constant(array $prose, string $name, array $values): string
    {
        $doc = [];
        foreach ($prose as $paragraph) {
            \array_push($doc, ...Php::wrap($paragraph, 4));
        }
        $doc[] = '';
        $doc[] = '@var list<string>';

        $head = '    public const array ' . $name . ' = [';
        $items = \array_map(Php::string(...), $values);
        $single = $head . \implode(', ', $items) . '];';
        $literal = \strlen($single) <= 120 || $items === []
            ? $single
            : $head . "\n" . \implode('', \array_map(static fn(string $item): string => '        ' . $item . ",\n", $items)) . '    ];';

        return Php::docblock($doc, 4, false) . $literal . "\n";
    }

    /** The path of a write operation, for a class's `@generated` line. */
    public static function operationLine(WriteModel $model, OperationKind $kind): string
    {
        $operation = $model->resource->operation($kind);

        return '@generated from `' . ($operation === null ? $kind->value : $operation->method . ' ' . $operation->path) . '`';
    }
}
