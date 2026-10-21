<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\ReadModel;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\AttributeDescriptor;
use haddowg\JsonApiCodegen\Descriptor\Cardinality;
use haddowg\JsonApiCodegen\Descriptor\OperationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Descriptor\RelationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Contribution;
use haddowg\JsonApiCodegen\Emit\Emitter;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\UnitKind;

/**
 * The read model of every resource type: the relation markers, the resource base and the
 * concrete resource class.
 *
 * The base carries every attribute with its native type, the always-safe linkage companions and
 * `_rel()`. The markers carry one hydrated relation each, so a query can intersect them into its
 * projection. The concrete class implements every marker, hydrates, and hosts the static write
 * factories and the type's `@phpstan-type` aliases, to which this emitter contributes the
 * attribute shapes.
 *
 * A relation gets a marker only when it can be hydrated: the type's `include` parameter names
 * it, and every type it can point at is generated. Anything else keeps its linkage companions
 * and is reached through `_rel()`.
 */
final class ReadModelEmitter implements Emitter
{
    /** Order of this emitter's contribution to the resource class: the attribute shapes come first. */
    public const int ORDER = 10;

    /**
     * Static members of the concrete class. An attribute or relation with one of these names
     * cannot also be an instance accessor there, so it is read through a fallback instead.
     */
    public const array RESERVED = ['create', 'update', 'ref', 'projection'];

    /** Alias suffixes other emitters declare on the resource class, which an attribute shape must not reuse. */
    private const array FOREIGN_ALIASES = ['CreateShape', 'UpdateShape', 'FilterShape', 'RangeShape', 'DateRangeShape'];

    private const string RUNTIME = 'haddowg\\JsonApiClient\\';

    private const string IDENTIFIER = self::RUNTIME . 'Support\\Identifier';

    private const string RELATIONSHIP = self::RUNTIME . 'Resources\\Relationship';

    private const string RESOURCE_OBJECT = self::RUNTIME . 'Resources\\ResourceObject';

    private const string RESOURCE_CONTEXT = self::RUNTIME . 'Resources\\ResourceContext';

    private const string COLLECTION = self::RUNTIME . 'Collections\\ResourceCollection';

    private const string FIELD_NOT_SELECTED = self::RUNTIME . 'Exceptions\\FieldNotSelectedException';

    private const string MALFORMED = self::RUNTIME . 'Exceptions\\MalformedDocument';

    private const string NOT_INCLUDED = self::RUNTIME . 'Exceptions\\RelationNotIncludedException';

    /** @var list<string> */
    private array $warnings = [];

    /**
     * Collisions this run routed through a fallback accessor, for the caller to surface.
     *
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    public function emit(ApiDescriptor $api, GeneratorConfig $config): iterable
    {
        $out = [];
        foreach ($api->resources as $resource) {
            \array_push($out, ...$this->resource($api, $resource, $config));
        }

        return $out;
    }

    /**
     * The relations of a type that are hydrated, and so get a marker, in document order.
     *
     * @return list<RelationDescriptor>
     */
    public static function hydratedRelations(ApiDescriptor $api, ResourceDescriptor $resource): array
    {
        return \array_values(\array_filter(
            self::relations($api, $resource),
            static fn(RelationDescriptor $relation): bool => self::hydrates($api, $resource, $relation),
        ));
    }

    public static function hydrates(ApiDescriptor $api, ResourceDescriptor $resource, RelationDescriptor $relation): bool
    {
        if (!\in_array($relation->name, $resource->includable, true) || $relation->types === []) {
            return false;
        }
        foreach ($relation->types as $type) {
            if ($api->resource($type) === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * The PHP accessor a member is read through: its own name, or `<name>Attribute` /
     * `<name>Relation` when the name is one of the concrete class's static factories.
     */
    public static function accessor(string $member, bool $relation): string
    {
        $method = Naming::member($member);

        return \in_array(\strtolower($method), self::RESERVED, true) ? $method . ($relation ? 'Relation' : 'Attribute') : $method;
    }

    /** @return list<ClassUnit|Contribution> */
    private function resource(ApiDescriptor $api, ResourceDescriptor $resource, GeneratorConfig $config): array
    {
        $type = $resource->type;
        $file = FileKey::resource($type);
        $hooks = $config->target->hasPropertyHooks();

        foreach ([...self::attributes($api, $resource), ...self::relations($api, $resource)] as $member) {
            if (Naming::member($member->name) !== self::accessor($member->name, $member instanceof RelationDescriptor)) {
                $this->warnings[] = \sprintf(
                    '"%s" on %s collides with the static %s::%s(); it is read through %s() instead.',
                    $member->name,
                    $type,
                    Naming::resource($type),
                    Naming::member($member->name),
                    self::accessor($member->name, $member instanceof RelationDescriptor),
                );
            }
        }

        $reads = \array_map(fn(AttributeDescriptor $attribute): AttributeRead => $this->attribute($api, $resource, $attribute), self::attributes($api, $resource));
        $relations = self::relations($api, $resource);
        $hydrated = self::hydratedRelations($api, $resource);
        self::assertNoCollisions($resource, $reads, $relations);

        $out = [];
        foreach ($hydrated as $relation) {
            $out[] = self::marker($resource, $relation, $file, $hooks);
        }
        $out[] = self::base($resource, $reads, $relations, $file, $hooks);
        $out[] = self::concrete($resource, $hydrated, $file, $hooks);

        $tags = [];
        foreach ($reads as $read) {
            \array_push($out, ...$read->classes);
            if ($read->alias !== null) {
                $tags[] = '@phpstan-type ' . $read->alias . ' ' . $read->aliasType;
            }
        }
        if ($tags !== []) {
            $out[] = new Contribution(Naming::resource($type), self::ORDER, $tags);
        }

        return $out;
    }

    /**
     * @param list<AttributeRead>      $reads
     * @param list<RelationDescriptor> $relations
     */
    private static function assertNoCollisions(ResourceDescriptor $resource, array $reads, array $relations): void
    {
        $names = ['id' => 'the id accessor', 'type' => 'the type accessor', '_rel' => '_rel()', '_hydrate' => '_hydrate()'];
        foreach (self::RESERVED as $reserved) {
            $names[$reserved] = 'the static ' . $reserved . '()';
        }
        $claim = static function (string $method, string $by) use (&$names, $resource): void {
            $key = \strtolower($method);
            if (isset($names[$key])) {
                throw new \LogicException(\sprintf('On %s, %s and %s would both generate %s().', $resource->type, $names[$key], $by, $method));
            }
            $names[$key] = $by;
        };

        foreach ($reads as $read) {
            $claim($read->method, 'attribute "' . $read->name . '"');
        }
        foreach ($relations as $relation) {
            $claim(self::accessor($relation->name, true), 'relation "' . $relation->name . '"');
            $claim(Naming::member($relation->name) . 'Ref', 'the linkage of relation "' . $relation->name . '"');
            $claim('has' . Naming::pascal($relation->name), 'the linkage test of relation "' . $relation->name . '"');
        }
    }

    private static function marker(ResourceDescriptor $resource, RelationDescriptor $relation, string $file, bool $hooks): ClassUnit
    {
        $type = $resource->type;
        $method = self::accessor($relation->name, true);
        $related = self::relatedType($relation);
        $many = $relation->cardinality === Cardinality::Many;
        $throws = '@throws \\' . self::NOT_INCLUDED . ' when the read did not include `' . $relation->name . '`';

        $doc = [
            ...Php::wrap('The `' . $relation->name . '` relation of ' . self::article($type) . ' `' . $type . '` resource, hydrated.'),
            '',
            ...Php::wrap('A marker carries no implementation. `' . Naming::includeMethod($relation->name) . '()` intersects it into a query\'s projection, which is what makes `' . $method . '()` type-check after that include and fail without it.'),
            '',
            '@generated from `components.schemas.' . self::schema($type) . '.properties.relationships.' . $relation->name . '`',
        ];

        if ($many) {
            $accessor = Php::docblock(['@return ResourceCollection<' . $related . '>', '', $throws], 4, false)
                . '    public function ' . $method . '(): ResourceCollection;' . "\n";
            $property = '    /** @var ResourceCollection<' . $related . '> */' . "\n"
                . '    public ResourceCollection $' . $method . ' { get; }' . "\n";
        } else {
            $accessor = Php::docblock([
                ...Php::wrap('The related `' . \implode('`, `', $relation->types) . '` resource, or null when the linkage is empty.', 4),
                '',
                $throws,
            ], 4, false)
                . '    public function ' . $method . '(): ' . self::nullable($related) . ';' . "\n";
            $property = '    public ' . self::nullable($related) . ' $' . $method . ' { get; }' . "\n";
        }

        $source = Php::docblock($doc, 0, false)
            . 'interface ' . Naming::marker($type, $relation->name) . "\n{\n"
            . $accessor
            . ($hooks ? "\n" . $property : '')
            . "}\n";

        return new ClassUnit(Naming::marker($type, $relation->name), UnitKind::Interface, $file, $source, $many ? [self::COLLECTION] : []);
    }

    /**
     * @param list<AttributeRead>      $reads
     * @param list<RelationDescriptor> $relations
     */
    private static function base(ResourceDescriptor $resource, array $reads, array $relations, string $file, bool $hooks): ClassUnit
    {
        $type = $resource->type;
        $class = Naming::resource($type);
        $imports = [self::RESOURCE_OBJECT];

        $doc = [...Php::wrap(\ucfirst(self::article($type)) . ' `' . $type . '` resource with no relation hydrated: everything a read is guaranteed to give you.'), ''];
        if ($reads !== []) {
            $doc = [...$doc, ...Php::wrap('Every attribute is here, because a sparse fieldset is a wire concern. Reading one the response did not carry throws rather than returning null, so an excluded attribute never reads as an empty one.'), ''];
        }
        $aliases = \array_values(\array_filter(\array_map(static fn(AttributeRead $read): ?string => $read->alias, $reads)));
        foreach ($aliases as $alias) {
            $doc[] = '@phpstan-import-type ' . $alias . ' from ' . $class;
        }
        if ($aliases !== []) {
            $doc[] = '';
        }
        $doc[] = '@generated from `components.schemas.' . self::schema($type) . '`';

        $members = [Php::docblock(['The resource type, which is a constant for a generated class and not a wire read.'], 4, false)
            . '    public const string TYPE = ' . Php::string($type) . ";\n"];

        if ($hooks) {
            $members[] = '    public string $id { get => $this->id(); }' . "\n";
            $members[] = '    public string $type { get => $this->type(); }' . "\n";
            foreach ($reads as $read) {
                $members[] = ($read->docType === null ? '' : '    /** @var ' . $read->docType . " */\n")
                    . '    public ' . $read->nativeType . ' $' . $read->method . ' { get => $this->' . $read->method . "(); }\n";
            }
        }

        $members[] = "    public function id(): string\n    {\n        return \$this->_identifier()->id;\n    }\n";
        $members[] = Php::docblock(['@return self::TYPE'], 4, false) . "    public function type(): string\n    {\n        return self::TYPE;\n    }\n";

        $readOnly = $resource->readOnlyAttributes();
        foreach ($reads as $read) {
            $lines = $read->prose === [] ? [] : [...$read->prose, ''];
            if (\in_array($read->name, $readOnly, true)) {
                \array_unshift($lines, 'Read-only: present on the resource, absent from every write shape.', '');
            }
            if ($read->docType !== null) {
                $lines[] = '@return ' . $read->docType;
                $lines[] = '';
            }
            $lines[] = '@throws FieldNotSelectedException when a sparse fieldset excluded `' . $read->name . '`';
            $members[] = Php::docblock($lines, 4, false)
                . '    public function ' . $read->method . '(): ' . $read->nativeType . "\n    {\n"
                . \implode("\n", Guard::indent($read->body, 8)) . "\n    }\n";
            \array_push($imports, self::FIELD_NOT_SELECTED, ...$read->imports);
        }

        foreach ($relations as $relation) {
            $member = Naming::member($relation->name);
            $toOne = $relation->cardinality === Cardinality::One ? 'true' : 'false';
            $call = '$this->_relationship(' . Php::string($relation->name) . ', ' . $toOne . ')';
            $imports[] = self::IDENTIFIER;
            if ($relation->cardinality === Cardinality::One) {
                $members[] = Php::docblock(['The `' . $relation->name . '` linkage, without hydrating anything. Never throws.'], 4, false)
                    . '    public function ' . $member . "Ref(): ?Identifier\n    {\n        return " . $call . "->identifier();\n    }\n";
                $members[] = Php::docblock(['Whether the `' . $relation->name . '` linkage points at anything. Never throws.'], 4, false)
                    . '    public function has' . Naming::pascal($relation->name) . "(): bool\n    {\n        return !" . $call . "->isEmpty();\n    }\n";
            } else {
                $members[] = Php::docblock(['The `' . $relation->name . '` linkage, without hydrating anything. Never throws.', '', '@return list<Identifier>'], 4, false)
                    . '    public function ' . $member . "Ref(): array\n    {\n        return " . $call . "->identifiers();\n    }\n";
                $members[] = Php::docblock(['Whether the `' . $relation->name . '` linkage points at anything. Never throws.'], 4, false)
                    . '    public function has' . Naming::pascal($relation->name) . "(): bool\n    {\n        return !" . $call . "->isEmpty();\n    }\n";
            }
        }

        if ($relations !== []) {
            $imports[] = self::RELATIONSHIP;
            $names = \array_map(static fn(RelationDescriptor $relation): string => $relation->name, $relations);
            $toOne = \array_values(\array_map(
                static fn(RelationDescriptor $relation): string => $relation->name,
                \array_filter($relations, static fn(RelationDescriptor $relation): bool => $relation->cardinality === Cardinality::One),
            ));
            $isToOne = match (true) {
                \count($toOne) === \count($names) => 'true',
                $toOne === [] => 'false',
                \count($toOne) === 1 => '$name === ' . Php::string($toOne[0]),
                default => '\in_array($name, [' . \implode(', ', \array_map(Php::string(...), $toOne)) . '], true)',
            };
            $members[] = Php::docblock([
                ...Php::wrap('A relationship object: linkage, links, and the `meta.total` a `withCount` read leaves behind. This is the door to a relation that carries no value.', 4),
                '',
                '@param ' . \implode('|', \array_map(Php::string(...), $names)) . ' $name',
            ], 4, false)
                . "    public function _rel(string \$name): Relationship\n    {\n        return \$this->_relationship(\$name, " . $isToOne . ");\n    }\n";
        }

        $source = Php::docblock($doc, 0, false)
            . 'abstract class ' . Naming::base($type) . " extends ResourceObject\n{\n"
            . \implode("\n", $members)
            . "}\n";

        return new ClassUnit(Naming::base($type), UnitKind::AbstractClass, $file, $source, \array_values(\array_unique($imports)));
    }

    /**
     * @param list<RelationDescriptor> $hydrated
     */
    private static function concrete(ResourceDescriptor $resource, array $hydrated, string $file, bool $hooks): ClassUnit
    {
        $type = $resource->type;
        $class = Naming::resource($type);
        $imports = [self::RESOURCE_CONTEXT, self::IDENTIFIER, self::MALFORMED];

        $doc = [
            ...Php::wrap(\ucfirst(self::article($type)) . ' `' . $type . '` resource as it actually arrives: every attribute, and every relation accessor.'),
            '',
            ...Php::wrap('The runtime value is always this class. A projection is a static narrowing over it, so a relation accessor exists at runtime whether or not it type-checks, and calling it without the include throws.'),
            '',
            ...Php::wrap('It also carries every array shape the `' . $type . '` surface accepts, and the descriptor constants the runtime guards check against. `@phpstan-type` aliases are class-local, so every other class using one imports it from here.'),
            '',
            '@generated from `components.schemas.' . self::schema($type) . '`',
        ];

        $members = [];
        if ($hooks) {
            foreach ($hydrated as $relation) {
                $method = self::accessor($relation->name, true);
                $related = self::relatedType($relation);
                $members[] = $relation->cardinality === Cardinality::Many
                    ? '    /** @var ResourceCollection<' . $related . "> */\n    public ResourceCollection \$" . $method . ' { get => $this->' . $method . "(); }\n"
                    : '    public ' . self::nullable($related) . ' $' . $method . ' { get => $this->' . $method . "(); }\n";
            }
        }

        $members[] = Php::docblock([
            'Build a resource from one resource object of an indexed response.',
            '',
            '@param array<string, mixed> $raw',
            '',
            '@throws MalformedDocument when the resource object is not ' . self::article($type) . ' `' . $type . '` one',
        ], 4, false)
            . "    public static function _hydrate(array \$raw, ResourceContext \$context): self\n    {\n"
            . '        $identifier = Identifier::fromArray($raw, ' . Php::string(self::article($type) . ' "') . " . self::TYPE . '\" resource object');\n\n"
            . "        if (\$identifier->type !== self::TYPE) {\n"
            . "            throw MalformedDocument::unexpectedMember(\n"
            . "                'The resource object',\n"
            . "                'type',\n"
            . "                '\"' . self::TYPE . '\" (got \"' . \$identifier->type . '\")',\n"
            . "            );\n"
            . "        }\n\n"
            . "        return new self(\$identifier, \$raw, \$context);\n    }\n";

        $members[] = Php::docblock([
            ...Php::wrap('A resource identifier for ' . self::article($type) . ' `' . $type . '` resource that is not in hand, for a relationship write.', 4),
            '',
            '@param array<string, mixed> $meta',
        ], 4, false)
            . "    public static function ref(string \$id, array \$meta = []): Identifier\n    {\n        return Identifier::of(self::TYPE, \$id, \$meta);\n    }\n";

        if (self::hasWriteProjection($resource)) {
            $projection = Naming::perType($type, 'Projection');
            $members[] = Php::docblock([
                ...Php::wrap('A reusable projection for a write response: `include` and `fields`, and nothing a write would be rejected for carrying.', 4),
                '',
                '@return ' . $projection . '<' . Naming::base($type) . '>',
            ], 4, false)
                . '    public static function projection(): ' . $projection . "\n    {\n        return " . $projection . "::make();\n    }\n";
        }

        if ($resource->operation(OperationKind::Create) !== null) {
            $create = Naming::perType($type, 'Create');
            $builder = Naming::perType($type, 'CreateBuilder');
            $members[] = Php::docblock([
                ...Php::wrap('The create input: a builder when called with nothing, or the create DTO built from an array whose keys are checked against the descriptor.', 4),
                '',
                '@param ' . Naming::alias($type, 'CreateShape') . '|null $input',
                '',
                '@return ($input is null ? ' . $builder . ' : ' . $create . ')',
                '',
                '@throws \\' . self::RUNTIME . 'Exceptions\\UnknownAttributeException when the array names a member `' . $type . '` does not have',
            ], 4, false)
                . '    public static function create(?array $input = null): ' . $builder . '|' . $create . "\n    {\n"
                . '        return $input === null ? new ' . $builder . '() : ' . $create . "::from(\$input);\n    }\n";
        }

        if ($resource->operation(OperationKind::Update) !== null) {
            $update = Naming::perType($type, 'Update');
            $members[] = Php::docblock([
                ...Php::wrap('The update input: an empty one to assemble fluently, or one built from an array whose keys are checked against the descriptor.', 4),
                '',
                '@param ' . Naming::alias($type, 'UpdateShape') . '|null $input',
                '',
                '@throws \\' . self::RUNTIME . 'Exceptions\\UnknownAttributeException when the array names a member `' . $type . '` does not have',
            ], 4, false)
                . '    public static function update(?array $input = null): ' . $update . "\n    {\n"
                . '        return $input === null ? new ' . $update . '() : ' . $update . "::from(\$input);\n    }\n";
        }

        foreach ($hydrated as $relation) {
            $members[] = self::relationAccessor($relation);
            $imports[] = self::NOT_INCLUDED;
            if ($relation->cardinality === Cardinality::Many) {
                $imports[] = self::COLLECTION;
            }
        }

        $implements = \array_map(static fn(RelationDescriptor $relation): string => Naming::marker($type, $relation->name), $hydrated);
        $source = Php::docblock($doc, 0, false)
            . 'final class ' . $class . ' extends ' . Naming::base($type)
            . ($implements === [] ? '' : ' implements ' . \implode(', ', $implements)) . "\n{\n"
            . \implode("\n", $members)
            . "}\n";

        return new ClassUnit($class, UnitKind::FinalClass, $file, $source, \array_values(\array_unique($imports)), true);
    }

    private static function relationAccessor(RelationDescriptor $relation): string
    {
        $name = $relation->name;
        $method = self::accessor($name, true);
        $companion = Php::string(Naming::member($name) . 'Ref');
        $related = self::relatedType($relation);
        $throws = '@throws RelationNotIncludedException when the read did not include `' . $name . '`';
        $classes = \array_map(Naming::resource(...), $relation->types);

        if ($relation->cardinality === Cardinality::One) {
            $doc = Php::docblock([$throws], 4, false);
            $signature = '    public function ' . $method . '(): ' . self::nullable($related) . "\n    {\n";
            $fetch = '$raw = $this->_includedOne(' . Php::string($name) . ', ' . $companion . ');';
            if (\count($classes) === 1) {
                return $doc . $signature
                    . '        ' . $fetch . "\n\n"
                    . '        return $raw === null ? null : ' . $classes[0] . '::_hydrate($raw, $this->_child(' . Php::string($name) . "));\n    }\n";
            }

            return $doc . $signature
                . '        ' . $fetch . "\n\n"
                . "        if (\$raw === null) {\n            return null;\n        }\n\n"
                . '        $context = $this->_child(' . Php::string($name) . ");\n\n"
                . "        return match (\$raw['type'] ?? null) {\n"
                . self::dispatch($relation, 12)
                . "        };\n    }\n";
        }

        $list = \in_array($method, ['raw', 'context'], true) ? $method . 'List' : $method;
        $hydrate = \count($classes) === 1
            ? '            $' . $list . '[] = ' . $classes[0] . "::_hydrate(\$raw, \$context);\n"
            : '            $' . $list . "[] = match (\$raw['type'] ?? null) {\n" . self::dispatch($relation, 16) . "            };\n";

        return Php::docblock(['@return ResourceCollection<' . $related . '>', '', $throws], 4, false)
            . '    public function ' . $method . "(): ResourceCollection\n    {\n"
            . '        $context = $this->_child(' . Php::string($name) . ");\n"
            . '        $' . $list . " = [];\n\n"
            . '        foreach ($this->_includedMany(' . Php::string($name) . ', ' . $companion . ") as \$raw) {\n"
            . $hydrate
            . "        }\n\n"
            . '        return new ResourceCollection($' . $list . ");\n    }\n";
    }

    /** The `match` arms hydrating a polymorphic relation's member by its `type`. */
    private static function dispatch(RelationDescriptor $relation, int $indent): string
    {
        $pad = \str_repeat(' ', $indent);
        $out = '';
        foreach ($relation->types as $type) {
            $out .= $pad . Naming::resource($type) . '::TYPE => ' . Naming::resource($type) . "::_hydrate(\$raw, \$context),\n";
        }
        $expected = 'one of "' . \implode('", "', $relation->types) . '"';

        return $out . $pad . 'default => throw MalformedDocument::unexpectedMember(' . Php::string('The "' . $relation->name . '" relationship') . ", 'type', " . Php::string($expected) . "),\n";
    }

    private function attribute(ApiDescriptor $api, ResourceDescriptor $resource, AttributeDescriptor $attribute): AttributeRead
    {
        $name = $attribute->name;
        $method = self::accessor($name, false);
        $field = Php::string($name);
        $nullable = $attribute->nullable;
        $prefix = $nullable ? 'Nullable' : '';

        if ($attribute->enum !== null && $api->enum($attribute->enum) !== null) {
            $reader = $nullable ? '_nullableEnum' : '_enum';

            return new AttributeRead($name, $method, self::nullable($attribute->enum, $nullable), null, ['return $this->' . $reader . '(' . $field . ', ' . $attribute->enum . '::class);']);
        }

        if (\in_array($attribute->format, ['date-time', 'date', 'time'], true)) {
            return new AttributeRead($name, $method, self::nullable('\DateTimeImmutable', $nullable), null, ['return $this->_' . \lcfirst($prefix . 'DateTime') . '(' . $field . ');']);
        }

        if ($attribute->schema === null) {
            return $this->scalar($resource, $attribute, self::jsonType($attribute->format), $method);
        }

        $shape = Shape::of($attribute->schema);
        $nullable = $nullable || $shape->nullable;
        $shape = $shape->nullable($nullable);

        return match ($shape->kind) {
            ShapeKind::Scalar => $this->scalar($resource, $attribute, (string) $shape->scalar, $method, $nullable),
            ShapeKind::Mixed => new AttributeRead($name, $method, 'mixed', null, ['return $this->_attribute(' . $field . ');']),
            ShapeKind::List => $this->list($resource, $attribute, $shape, $method),
            ShapeKind::Object => $this->object($resource, $attribute, $shape, $method),
            ShapeKind::Tagged => $this->tagged($resource, $attribute, $shape, $method),
        };
    }

    private function scalar(ResourceDescriptor $resource, AttributeDescriptor $attribute, string $json, string $method, ?bool $nullable = null): AttributeRead
    {
        $nullable ??= $attribute->nullable;
        $field = Php::string($attribute->name);

        if ($json === 'unknown') {
            return new AttributeRead($attribute->name, $method, 'mixed', null, ['return $this->_attribute(' . $field . ');']);
        }

        $reader = match ($json) {
            'integer' => $nullable ? null : '_int',
            'number' => $nullable ? '_nullableFloat' : '_float',
            'boolean' => $nullable ? null : '_bool',
            default => $nullable ? '_nullableString' : '_string',
        };
        if ($reader === null) {
            throw self::unreadable($resource, $attribute, 'a nullable ' . $json);
        }

        return new AttributeRead($attribute->name, $method, self::nullable(Shape::scalarType($json), $nullable), null, ['return $this->' . $reader . '(' . $field . ');']);
    }

    private function list(ResourceDescriptor $resource, AttributeDescriptor $attribute, Shape $shape, string $method): AttributeRead
    {
        $items = $shape->items;
        if ($shape->nullable || $items === null || $items->kind !== ShapeKind::Scalar || $items->scalar !== 'string' || $items->nullable) {
            throw self::unreadable($resource, $attribute, 'a ' . ($shape->nullable ? 'nullable ' : '') . $shape->wireType() . ' list');
        }

        return new AttributeRead($attribute->name, $method, 'array', 'list<string>', ['return $this->_stringList(' . Php::string($attribute->name) . ');']);
    }

    private function object(ResourceDescriptor $resource, AttributeDescriptor $attribute, Shape $shape, string $method): AttributeRead
    {
        if (!$shape->nullable) {
            throw self::unreadable($resource, $attribute, 'a non-nullable object');
        }
        $field = Php::string($attribute->name);

        if ($shape->members === []) {
            return new AttributeRead($attribute->name, $method, '?array', 'array<string, mixed>|null', ['return $this->_nullableMap(' . $field . ');']);
        }

        $alias = self::alias($resource, $method);
        $guard = new Guard('The "' . $attribute->name . '" attribute of ' . self::article($resource->type) . ' "' . $resource->type . '" resource', ['raw', 'shape']);
        $body = [
            '$raw = $this->_nullableMap(' . $field . ');',
            '',
            'if ($raw === null) {',
            '    return null;',
            '}',
            '',
            ...$guard->object($shape->members, 'raw', 'shape', ''),
            '',
            'return $shape;',
        ];
        $required = \array_filter($shape->members, static fn(array $member): bool => $member['required']);

        return new AttributeRead(
            $attribute->name,
            $method,
            '?array',
            $alias . '|null',
            $body,
            Php::wrap($required === []
                ? 'A nested object attribute, narrowed member by member: a member of the wrong type is dropped rather than trusted.'
                : 'A nested object attribute, narrowed member by member: an optional member of the wrong type is dropped rather than trusted, and a required one throws.', 4),
            $alias,
            $shape->nullable(false)->wireType(),
            $required === [] ? [] : [self::MALFORMED],
        );
    }

    private function tagged(ResourceDescriptor $resource, AttributeDescriptor $attribute, Shape $shape, string $method): AttributeRead
    {
        if (!$shape->nullable) {
            throw self::unreadable($resource, $attribute, 'a non-nullable tagged union');
        }
        $discriminator = (string) $shape->discriminator;
        $constant = \strtoupper((string) \preg_replace('/(?<=[a-z0-9])(?=[A-Z])/', '_', Naming::pascal($discriminator)));
        $file = FileKey::resource($resource->type);

        $classes = [];
        $arms = [];
        $values = [];
        foreach ($shape->branches as $branch) {
            $class = Naming::resource($resource->type) . Naming::pascal($method) . Naming::pascal($branch['value']);
            $classes[] = $this->branch($resource, $attribute, $class, $constant, $discriminator, $branch['value'], $branch['shape'], $file);
            $arms[] = '    ' . $class . '::' . $constant . ' => ' . $class . '::_from($raw),';
            $values[] = '"' . $branch['value'] . '"';
        }
        $names = \array_map(static fn(ClassUnit $unit): string => $unit->name, $classes);

        $body = [
            '$raw = $this->_nullableMap(' . Php::string($attribute->name) . ');',
            '',
            'if ($raw === null) {',
            '    return null;',
            '}',
            '',
            'return match ($raw[' . Php::string($discriminator) . '] ?? null) {',
            ...$arms,
            '    default => throw MalformedDocument::unexpectedMember(' . Php::string('The "' . $attribute->name . '" attribute of ' . self::article($resource->type) . ' "' . $resource->type . '" resource') . ', ' . Php::string($discriminator) . ', ' . Php::string('one of ' . \implode(', ', $values)) . '),',
            '};',
        ];

        return new AttributeRead(
            $attribute->name,
            $method,
            \implode('|', $names) . '|null',
            null,
            $body,
            Php::wrap('A tagged union, one class per branch, selected by `' . $discriminator . '`. Narrow it with `instanceof` or `match`.', 4),
            self::alias($resource, $method),
            $shape->nullable(false)->wireType(),
            [self::MALFORMED],
            $classes,
        );
    }

    /**
     * One branch of a tagged union: a value class with a property per member, the discriminator
     * excepted, since the class itself is the discriminator.
     */
    private function branch(ResourceDescriptor $resource, AttributeDescriptor $attribute, string $class, string $constant, string $discriminator, string $value, Shape $shape, string $file): ClassUnit
    {
        $members = $shape->members;
        unset($members[$discriminator]);
        \uasort($members, static fn(array $a, array $b): int => (int) $b['required'] <=> (int) $a['required']);

        $guard = new Guard('The "' . $attribute->name . '" attribute of ' . self::article($resource->type) . ' "' . $resource->type . '" resource', ['raw']);
        $params = [];
        $paramDocs = [];
        $extract = [];
        $statements = [];
        $arguments = [];
        $properties = [];
        foreach ($members as $name => $member) {
            $name = (string) $name;
            $property = Naming::member($name);
            if (isset($properties[\strtolower($property)])) {
                throw new \LogicException(\sprintf('%s: members "%s" and "%s" would both generate $%s.', $class, $properties[\strtolower($property)], $name, $property));
            }
            $properties[\strtolower($property)] = $name;

            $memberShape = $member['shape']->merged();
            $optional = !$member['required'];
            $typed = $optional ? $memberShape->nullable(true) : $memberShape;
            $native = self::native($typed);
            $params[] = '        public readonly ' . $native . ' $' . $property . ($optional ? ' = null' : '') . ',';
            if (\in_array($memberShape->kind, [ShapeKind::Object, ShapeKind::List], true)) {
                $paramDocs[] = '@param ' . $typed->wireType() . ' $' . $property;
            }

            $in = $guard->variable($property);
            $out = $guard->variable($property . 'Value');
            $extract[] = '$' . $in . ' = $raw[' . Php::string($name) . '] ?? null;';
            $statements[] = '$' . $out . ' = null;';
            $statements = [...$statements, ...$guard->value($memberShape, $in, '$' . $out, $member['required'], null, $name), ''];
            $arguments[] = '$' . $out;
        }

        $doc = [
            ...Php::wrap('The `' . $value . '` branch of the `' . $attribute->name . '` attribute of ' . self::article($resource->type) . ' `' . $resource->type . '` resource, selected by `' . $discriminator . '`.'),
            '',
            '@generated from `components.schemas.' . self::schema($resource->type) . '`, the `' . $value . '` branch of `' . $attribute->name . '`',
        ];

        $body = [Php::docblock(['The `' . $discriminator . '` value that selects this branch.'], 4, false)
            . '    public const string ' . $constant . ' = ' . Php::string($value) . ";\n"];
        $body[] = ($paramDocs === [] ? '' : Php::docblock($paramDocs, 4, false))
            . '    public function __construct(' . ($params === [] ? ") {}\n" : "\n" . \implode("\n", $params) . "\n    ) {}\n");

        $from = [...$extract, ...($extract === [] ? [] : ['']), ...$statements, 'return new self(' . \implode(', ', $arguments) . ');'];
        $body[] = Php::docblock(['@param array<string, mixed> $raw'], 4, false)
            . "    public static function _from(array \$raw): self\n    {\n"
            . \implode("\n", Guard::indent($from, 8)) . "\n    }\n";

        $source = Php::docblock($doc, 0, false)
            . 'final class ' . $class . "\n{\n"
            . \implode("\n", $body)
            . "}\n";

        $required = \array_filter($members, static fn(array $member): bool => $member['required']);

        return new ClassUnit($class, UnitKind::FinalClass, $file, $source, $required === [] ? [] : [self::MALFORMED]);
    }

    private static function native(Shape $shape): string
    {
        $type = match ($shape->kind) {
            ShapeKind::Mixed => 'mixed',
            ShapeKind::Scalar => Shape::scalarType((string) $shape->scalar),
            default => 'array',
        };

        return $type === 'mixed' ? $type : self::nullable($type, $shape->nullable);
    }

    private static function alias(ResourceDescriptor $resource, string $method): string
    {
        $suffix = Naming::pascal($method) . 'Shape';

        return Naming::alias($resource->type, \in_array($suffix, self::FOREIGN_ALIASES, true) ? Naming::pascal($method) . 'AttributeShape' : $suffix);
    }

    private static function unreadable(ResourceDescriptor $resource, AttributeDescriptor $attribute, string $what): \LogicException
    {
        return new \LogicException(\sprintf('The "%s" attribute of %s is %s, which the json-api-client runtime has no reader for.', $attribute->name, $resource->type, $what));
    }

    /** The JSON type a format hint stands for. Formats the descriptor passes through are string ones unless they say otherwise. */
    private static function jsonType(string $format): string
    {
        return match ($format) {
            'integer', 'int32', 'int64' => 'integer',
            'number', 'float', 'double' => 'number',
            'boolean' => 'boolean',
            'unknown' => 'unknown',
            default => 'string',
        };
    }

    private static function relatedType(RelationDescriptor $relation): string
    {
        return \implode('|', \array_map(Naming::resource(...), $relation->types));
    }

    private static function nullable(string $type, bool $nullable = true): string
    {
        if (!$nullable) {
            return $type;
        }

        return \str_contains($type, '|') ? $type . '|null' : '?' . $type;
    }

    private static function hasWriteProjection(ResourceDescriptor $resource): bool
    {
        foreach ([OperationKind::Create, OperationKind::Update] as $kind) {
            if ($resource->operation($kind)?->projection !== null) {
                return true;
            }
        }

        return false;
    }

    /** `albums` → `AlbumsResource`, the component the resource object is described by. */
    private static function schema(string $type): string
    {
        return Naming::pascal($type) . 'Resource';
    }

    private static function article(string $type): string
    {
        return \in_array(\strtolower($type[0] ?? ''), ['a', 'e', 'i', 'o', 'u'], true) ? 'an' : 'a';
    }

    /**
     * Attributes in document order.
     *
     * @return list<AttributeDescriptor>
     */
    private static function attributes(ApiDescriptor $api, ResourceDescriptor $resource): array
    {
        return self::ordered(\array_values($resource->attributes), self::documentOrder($api, $resource));
    }

    /**
     * Relations in document order.
     *
     * @return list<RelationDescriptor>
     */
    private static function relations(ApiDescriptor $api, ResourceDescriptor $resource): array
    {
        return self::ordered(\array_values($resource->relations), self::documentOrder($api, $resource));
    }

    /**
     * The descriptor keys members by name; a type's fieldset tokens are the one place it keeps
     * the order the document declares them in, which is the order generated code lists them.
     *
     * @return list<string>
     */
    private static function documentOrder(ApiDescriptor $api, ResourceDescriptor $resource): array
    {
        /** @var list<OperationDescriptor|null> $candidates */
        $candidates = [$resource->operation(OperationKind::FetchOne), $resource->operation(OperationKind::FetchMany), ...\array_values($resource->operations)];
        foreach ($api->resources as $other) {
            \array_push($candidates, ...\array_values($other->operations));
            foreach ($other->relations as $relation) {
                $candidates[] = $relation->relatedRead;
            }
        }

        foreach ($candidates as $operation) {
            $fieldset = $operation?->projection?->fieldsets[$resource->type] ?? null;
            if ($fieldset !== null) {
                return $fieldset;
            }
        }

        return [];
    }

    /**
     * @template T of AttributeDescriptor|RelationDescriptor
     *
     * @param list<T>      $members
     * @param list<string> $order
     *
     * @return list<T>
     */
    private static function ordered(array $members, array $order): array
    {
        $position = \array_flip($order);
        $indexed = [];
        foreach ($members as $index => $member) {
            $indexed[] = [$position[$member->name] ?? \PHP_INT_MAX, $index, $member];
        }
        \usort($indexed, static fn(array $a, array $b): int => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        $out = [];
        foreach ($indexed as [, , $member]) {
            $out[] = $member;
        }

        return $out;
    }
}
