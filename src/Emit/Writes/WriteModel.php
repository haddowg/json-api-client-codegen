<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Writes;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\Cardinality;
use haddowg\JsonApiCodegen\Descriptor\ClientIdPolicy;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Descriptor\WriteAttributeDescriptor;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Narrowing;

/**
 * The write surface of one resource type: its create and update members, in the order the
 * generated code declares them, and the aliases they are typed by.
 */
final class WriteModel
{
    /** Methods the write classes declare themselves, which a member's setter must not take. */
    private const array RESERVED_METHODS = ['build', 'from', 'toDocument', 'when', 'unless', 'tap', 'conditionHolds', 'id'];

    /**
     * @param list<WriteMember>|null $create null when the type has no create operation
     * @param list<WriteMember>|null $update null when the type has no update operation
     */
    private function __construct(
        public readonly ResourceDescriptor $resource,
        public readonly ?array $create,
        public readonly ?array $update,
    ) {}

    public static function of(ApiDescriptor $api, ResourceDescriptor $resource): self
    {
        $create = $resource->operation(OperationKind::Create) === null
            ? null
            : self::members($api, $resource, $resource->createAttributes, $resource->createRelations);
        $update = $resource->operation(OperationKind::Update) === null
            ? null
            : self::members($api, $resource, $resource->updateAttributes, $resource->updateRelations, false);

        return new self($resource, $create, $update);
    }

    public function writes(): bool
    {
        return $this->create !== null || $this->update !== null;
    }

    public function clientId(): ClientIdPolicy
    {
        return $this->resource->clientId;
    }

    /**
     * Every member either write accepts, in document order.
     *
     * @return list<string>
     */
    public function writable(): array
    {
        $names = [];
        foreach ([...$this->create ?? [], ...$this->update ?? []] as $member) {
            $names[$member->name] = true;
        }

        return self::documentOrder($this->resource, \array_keys($names));
    }

    /**
     * @return list<string>
     */
    public function requiredOnCreate(): array
    {
        return \array_values(\array_map(
            static fn(WriteMember $member): string => $member->name,
            \array_filter($this->create ?? [], static fn(WriteMember $member): bool => $member->required),
        ));
    }

    /**
     * @param list<WriteMember> $members
     *
     * @return list<string>
     */
    public static function names(array $members): array
    {
        return \array_map(static fn(WriteMember $member): string => $member->name, $members);
    }

    /**
     * @param array<string, WriteAttributeDescriptor> $attributes
     * @param list<string>                            $relations
     *
     * @return list<WriteMember>
     */
    private static function members(ApiDescriptor $api, ResourceDescriptor $resource, array $attributes, array $relations, bool $honourRequired = true): array
    {
        $type = $resource->type;
        $members = [];

        foreach (self::documentOrder($resource, \array_keys($attributes)) as $name) {
            $members[] = self::attribute($api, $resource, $attributes[$name], $honourRequired && $attributes[$name]->required);
        }

        foreach ($relations as $name) {
            $relation = $resource->writableRelation($name);
            if ($relation === null) {
                continue;
            }
            $classes = [];
            foreach ($relation->types as $related) {
                if ($api->resource($related) !== null) {
                    $classes[] = Naming::resource($related);
                }
            }
            $classes[] = 'Identifier';
            $classes = \array_values(\array_unique($classes));
            \sort($classes, \SORT_STRING);
            $union = \implode('|', $classes);
            $toMany = $relation->cardinality === Cardinality::Many;

            $members[] = new WriteMember(
                $name,
                Naming::member($name),
                self::setter($name),
                true,
                false,
                !$toMany,
                $toMany ? 'array' : $union,
                $toMany ? 'list<' . $union . '>' : $union,
                $toMany ? 'list<' . $union . '>' : $union,
                Naming::alias($type, Naming::pascal($name) . 'Input'),
                Coercion::None,
                null,
                null,
                $toMany,
            );
        }

        $seen = [];
        foreach ($members as $member) {
            if (isset($seen[$member->variable])) {
                throw new \LogicException(\sprintf('The "%s" members "%s" and "%s" both generate the PHP name "$%s".', $type, $seen[$member->variable], $member->name, $member->variable));
            }
            $seen[$member->variable] = $member->name;
        }

        $required = \array_values(\array_filter($members, static fn(WriteMember $member): bool => $member->required));
        $optional = \array_values(\array_filter($members, static fn(WriteMember $member): bool => !$member->required));

        return [...$required, ...$optional];
    }

    private static function attribute(ApiDescriptor $api, ResourceDescriptor $resource, WriteAttributeDescriptor $attribute, bool $required): WriteMember
    {
        $type = $resource->type;
        $name = $attribute->name;
        $enum = $attribute->enum !== null && $api->enum($attribute->enum) !== null ? $attribute->enum : null;

        $dateFormat = match ($attribute->format) {
            'date-time' => '\DateTimeInterface::ATOM',
            'date' => "'Y-m-d'",
            'time' => "'H:i:s'",
            default => null,
        };

        if ($enum !== null) {
            $native = $doc = $aliasDoc = $enum;
            $input = Naming::alias($type, Naming::pascal($name) . 'Input');
            $coercion = Coercion::Enum;
        } elseif ($dateFormat !== null) {
            $native = $doc = $aliasDoc = '\DateTimeImmutable';
            $input = Naming::alias($type, 'DateInput');
            $coercion = Coercion::Date;
        } else {
            [$native, $doc] = self::value($attribute);
            $aliasDoc = self::readShapeAlias($resource, $attribute) ?? $doc;
            $input = $aliasDoc;
            $coercion = Coercion::None;
        }

        $nullable = $attribute->nullable && $native !== 'mixed';

        return new WriteMember(
            $name,
            Naming::member($name),
            self::setter($name),
            false,
            $required,
            $nullable,
            $native,
            $doc,
            $aliasDoc,
            $nullable ? $input . '|null' : $input,
            $coercion,
            $enum,
            $dateFormat,
            false,
            $enum === null && $dateFormat === null ? self::prose($attribute) : null,
            $enum !== null && ($api->enum($enum)?->intBacked() ?? false),
        );
    }

    /** The constraints of a write attribute that its narrowed type cannot state. */
    private static function prose(WriteAttributeDescriptor $attribute): ?string
    {
        $json = null;
        foreach ($attribute->schema->types ?? [] as $type) {
            if ($type !== 'null') {
                $json = $type;
                break;
            }
        }
        $json ??= match ($attribute->format) {
            'integer', 'int32', 'int64' => 'integer',
            'number', 'float', 'double' => 'number',
            'boolean', 'object', 'array', 'unknown' => $attribute->format,
            default => 'string',
        };

        return Narrowing::prose($json, $attribute->constraints, $attribute->format);
    }

    /**
     * @return array{string, string} the native type and the doc type
     */
    private static function value(WriteAttributeDescriptor $attribute): array
    {
        if ($attribute->schema !== null) {
            $shape = Shape::of($attribute->schema);

            return [$shape->native, $shape->doc];
        }

        return match ($attribute->format) {
            'integer', 'int32', 'int64' => ['int', Narrowing::scalar('integer', $attribute->constraints)],
            'number', 'float', 'double' => ['float', 'float'],
            'boolean' => ['bool', 'bool'],
            'object' => ['array', 'array<string, mixed>'],
            'array' => ['array', 'list<mixed>'],
            'unknown' => ['mixed', 'mixed'],
            default => ['string', Narrowing::scalar('string', $attribute->constraints)],
        };
    }

    /**
     * The read model's alias for an object attribute's shape, when the read side declares the
     * same plain object. A composed or diverging write shape is written out instead.
     */
    private static function readShapeAlias(ResourceDescriptor $resource, WriteAttributeDescriptor $attribute): ?string
    {
        $read = $resource->attributes[$attribute->name] ?? null;
        if ($read === null || $read->schema === null || $attribute->schema === null) {
            return null;
        }
        if ($read->format !== 'object' || $read->schema->composed() || $read->schema->properties === []) {
            return null;
        }
        if (Shape::of($read->schema)->doc !== Shape::of($attribute->schema)->doc) {
            return null;
        }

        return Naming::alias($resource->type, Naming::pascal($attribute->name) . 'Shape');
    }

    private static function setter(string $name): string
    {
        $method = Naming::member($name);

        return \in_array($method, self::RESERVED_METHODS, true) ? 'set' . Naming::pascal($name) : $method;
    }

    /**
     * @param list<string> $names
     *
     * @return list<string>
     */
    private static function documentOrder(ResourceDescriptor $resource, array $names): array
    {
        return $resource->inDocumentOrder($names);
    }
}
