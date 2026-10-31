<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\ReadModel;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\AttributeDescriptor;
use haddowg\JsonApiCodegen\Descriptor\Cardinality;
use haddowg\JsonApiCodegen\Descriptor\Constraints;
use haddowg\JsonApiCodegen\Descriptor\DeclaredLinkDescriptor;
use haddowg\JsonApiCodegen\Descriptor\LinkMetaDescriptor;
use haddowg\JsonApiCodegen\Descriptor\OperationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Descriptor\RelationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Contribution;
use haddowg\JsonApiCodegen\Emit\Emitter;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\Identifier;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Narrowing;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\Surface\SurfaceEmitter;
use haddowg\JsonApiCodegen\Emit\UnitKind;
use haddowg\JsonApiCodegen\Emit\Warns;

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
final class ReadModelEmitter implements Emitter, Warns
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
        $out[] = self::base($resource, $reads, $relations, $file, $hooks, SurfaceEmitter::hasHandle($api, $resource));
        $out[] = self::concrete($resource, $hydrated, $file, $hooks);
        foreach ($relations as $relation) {
            if (self::readsPivot($relation)) {
                $out[] = $this->pivot($api, $resource, $relation, $file, $hooks);
            }
        }
        if ($resource->links !== []) {
            \array_push($out, ...$this->declaredLinks($api, $resource, $file, $hooks));
        }

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
        $names = ['id' => 'the id accessor', 'type' => 'the type accessor', '_rel' => '_rel()', '_hydrate' => '_hydrate()', '_handle' => '_handle()'];
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
            if (self::readsPivot($relation)) {
                $claim(self::pivotAccessor($relation), 'the pivot of relation "' . $relation->name . '"');
            }
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
    private static function base(ResourceDescriptor $resource, array $reads, array $relations, string $file, bool $hooks, bool $handle): ClassUnit
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
                $members[] = ($read->docType === null ? '' : '    ' . Php::comment('@var ' . $read->docType) . "\n")
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
            if (self::readsPivot($relation)) {
                $members[] = self::pivotsAccessor($relation, $call, $type);
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
                '@param ' . \implode('|', \array_map(Php::docString(...), $names)) . ' $name',
            ], 4, false)
                . "    public function _rel(string \$name): Relationship\n    {\n        return \$this->_relationship(\$name, " . $isToOne . ");\n    }\n";
        }

        if ($resource->links !== []) {
            $members[] = Php::docblock(Php::wrap('The links ' . self::article($type) . ' `' . $type . '` resource declares, each typed. A link the response left out reads as null. Every link, declared or not, is in `_links()`.', 4), 4, false)
                . '    public function _declaredLinks(): ' . self::linksClass($type) . "\n    {\n"
                . '        return new ' . self::linksClass($type) . "(\$this->_links(), \$this->_subject());\n    }\n";
        }

        if ($handle) {
            $label = \str_replace('-', ' ', Naming::singular($type));
            $members[] = Php::docblock([
                ...Php::wrap('This ' . $label . '\'s id-handle, to write through: its update, delete, relationship writes and actions, exactly as `$client->' . Naming::member($type) . '->id(…)` offers them.', 4),
                '',
                '@throws \LogicException when the ' . $label . ' was not read through a client',
            ], 4, false)
                . '    public function _handle(): ' . Naming::perType($type, 'Handle') . "\n    {\n"
                . '        return new ' . Naming::perType($type, 'Handle') . "(\$this->_fetcher(), \$this->id());\n"
                . "    }\n";
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
            if (\count($classes) === 1 && !$relation->polymorphic) {
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
        $hydrate = \count($classes) === 1 && !$relation->polymorphic
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
            $json = self::jsonType($attribute->format);

            return $this->scalar($resource, $attribute, $json, $method)
                ->withProse(Narrowing::prose($json, $attribute->constraints, $attribute->format));
        }

        $shape = Shape::of($attribute->schema);
        $nullable = $nullable || $shape->nullable;
        $shape = $shape->nullable($nullable);

        $read = match ($shape->kind) {
            ShapeKind::Scalar => $this->scalar($resource, $attribute, (string) $shape->scalar, $method, $nullable, $shape->constraints),
            ShapeKind::Mixed => new AttributeRead($name, $method, 'mixed', null, ['return $this->_attribute(' . $field . ');']),
            ShapeKind::List => $this->list($resource, $attribute, $shape, $method),
            ShapeKind::Object => $this->object($resource, $attribute, $shape, $method),
            ShapeKind::Tagged => $this->tagged($resource, $attribute, $shape, $method),
        };

        return $read->withProse(match ($shape->kind) {
            ShapeKind::Scalar => Narrowing::prose((string) $shape->scalar, $shape->constraints, $attribute->schema->format),
            ShapeKind::List => Narrowing::prose('array', $shape->constraints),
            ShapeKind::Object => Narrowing::prose('object', $attribute->schema->constraints),
            default => null,
        });
    }

    private function scalar(ResourceDescriptor $resource, AttributeDescriptor $attribute, string $json, string $method, ?bool $nullable = null, ?Constraints $constraints = null): AttributeRead
    {
        $nullable ??= $attribute->nullable;
        $field = Php::string($attribute->name);

        if ($json === 'unknown') {
            return new AttributeRead($attribute->name, $method, 'mixed', null, ['return $this->_attribute(' . $field . ');']);
        }

        $reader = match ($json) {
            'integer' => $nullable ? '_nullableInt' : '_int',
            'number' => $nullable ? '_nullableFloat' : '_float',
            'boolean' => $nullable ? '_nullableBool' : '_bool',
            default => $nullable ? '_nullableString' : '_string',
        };
        $native = self::nullable(Shape::scalarType($json), $nullable);
        $call = '$this->' . $reader . '(' . $field . ')';

        $narrowed = Narrowing::scalar($json, $constraints ?? $attribute->constraints);
        if ($narrowed === Shape::scalarType($json)) {
            return new AttributeRead($attribute->name, $method, $native, null, ['return ' . $call . ';']);
        }

        $doc = $narrowed . ($nullable ? '|null' : '');

        return new AttributeRead($attribute->name, $method, $native, $doc, [Php::comment('@var ' . $doc . ' $value'), '$value = ' . $call . ';', '', 'return $value;']);
    }

    private function list(ResourceDescriptor $resource, AttributeDescriptor $attribute, Shape $shape, string $method): AttributeRead
    {
        $items = $shape->items;
        if ($items === null || $items->kind !== ShapeKind::Scalar) {
            throw self::unreadable($resource, $attribute, 'a list of ' . ($items === null ? 'unknown members' : $items->wireType() . ' values'));
        }
        $field = Php::string($attribute->name);

        if (!$shape->nullable && $items->scalar === 'string' && !$items->nullable) {
            if (!$shape->narrowed()) {
                return new AttributeRead($attribute->name, $method, 'array', 'list<string>', ['return $this->_stringList(' . $field . ');']);
            }

            return new AttributeRead($attribute->name, $method, 'array', $shape->wireType(), [Php::comment('@var ' . $shape->wireType() . ' $value'), '$value = $this->_stringList(' . $field . ');', '', 'return $value;']);
        }

        $json = (string) $items->scalar;
        $type = Shape::scalarType($json);
        $check = match ($json) {
            'integer' => '\\is_int($member)',
            'number' => '\\is_int($member) || \\is_float($member)',
            'boolean' => '\\is_bool($member)',
            default => '\\is_string($member)',
        };
        if ($items->nullable) {
            $check = '$member === null || ' . $check;
        }
        $value = $json === 'number' ? ($items->nullable ? '$member === null ? null : (float) $member' : '(float) $member') : '$member';
        $memberType = $items->nullable ? $type . '|null' : $type;
        $listType = $shape->nullable(false)->wireType();
        $subject = 'The "' . $attribute->name . '" attribute of ' . self::article($resource->type) . ' "' . $resource->type . '" resource';

        return new AttributeRead(
            $attribute->name,
            $method,
            $shape->nullable ? '?array' : 'array',
            $listType . ($shape->nullable ? '|null' : ''),
            [
                '$raw = $this->' . ($shape->nullable ? '_nullableList' : '_list') . '(' . $field . ');',
                '',
                ...($shape->nullable ? ['if ($raw === null) {', '    return null;', '}', ''] : []),
                '$list = [];',
                'foreach ($raw as $member) {',
                '    if (!(' . $check . ')) {',
                '        throw MalformedDocument::unexpectedMember(' . Php::string($subject) . ', ' . $field . ', ' . Php::string('a list of ' . $memberType . ' values') . ');',
                '    }',
                '    $list[] = ' . $value . ';',
                '}',
                '',
                ...($shape->narrowed() ? [Php::comment('@var ' . $listType . ' $list')] : []),
                'return $list;',
            ],
            [],
            null,
            null,
            [self::MALFORMED],
        );
    }

    private function object(ResourceDescriptor $resource, AttributeDescriptor $attribute, Shape $shape, string $method): AttributeRead
    {
        $nullable = $shape->nullable;
        $reader = $nullable ? '_nullableMap' : '_map';
        $field = Php::string($attribute->name);

        if ($shape->members === []) {
            return new AttributeRead($attribute->name, $method, $nullable ? '?array' : 'array', 'array<string, mixed>' . ($nullable ? '|null' : ''), ['return $this->' . $reader . '(' . $field . ');']);
        }

        $alias = self::alias($resource, $method);
        $guard = new Guard('The "' . $attribute->name . '" attribute of ' . self::article($resource->type) . ' "' . $resource->type . '" resource', ['raw', 'shape']);
        $body = [
            '$raw = $this->' . $reader . '(' . $field . ');',
            '',
            ...($nullable ? ['if ($raw === null) {', '    return null;', '}', ''] : []),
            ...$guard->object($shape->members, 'raw', 'shape', ''),
            '',
            'return $shape;',
        ];
        $required = \array_filter($shape->members, static fn(array $member): bool => $member['required']);

        return new AttributeRead(
            $attribute->name,
            $method,
            $nullable ? '?array' : 'array',
            $alias . ($nullable ? '|null' : ''),
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
        $nullable = $shape->nullable;
        $discriminator = (string) $shape->discriminator;
        $constant = \strtoupper((string) \preg_replace('/(?<=[a-z0-9])(?=[A-Z])/', '_', Naming::pascal($discriminator)));
        if (!Identifier::isConstantName($constant)) {
            $this->warnings[] = \sprintf('The discriminator %s of %s.%s cannot name a PHP constant; each branch class carries its value as DISCRIMINATOR instead.', \json_encode($discriminator, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_INVALID_UTF8_SUBSTITUTE), $resource->type, $attribute->name);
            $constant = 'DISCRIMINATOR';
        }
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
            '$raw = $this->' . ($nullable ? '_nullableMap' : '_map') . '(' . Php::string($attribute->name) . ');',
            '',
            ...($nullable ? ['if ($raw === null) {', '    return null;', '}', ''] : []),
            'return match ($raw[' . Php::string($discriminator) . '] ?? null) {',
            ...$arms,
            '    default => throw MalformedDocument::unexpectedMember(' . Php::string('The "' . $attribute->name . '" attribute of ' . self::article($resource->type) . ' "' . $resource->type . '" resource') . ', ' . Php::string($discriminator) . ', ' . Php::string('one of ' . \implode(', ', $values)) . '),',
            '};',
        ];

        return new AttributeRead(
            $attribute->name,
            $method,
            \implode('|', $names) . ($nullable ? '|null' : ''),
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

        $doc = [
            ...Php::wrap('The `' . $value . '` branch of the `' . $attribute->name . '` attribute of ' . self::article($resource->type) . ' `' . $resource->type . '` resource, selected by `' . $discriminator . '`.'),
            '',
            '@generated from `components.schemas.' . self::schema($resource->type) . '`, the `' . $value . '` branch of `' . $attribute->name . '`',
        ];
        $const = Php::docblock(['The `' . $discriminator . '` value that selects this branch.'], 4, false)
            . '    public const string ' . $constant . ' = ' . Php::string($value) . ";\n";

        return self::valueClass($class, 'The "' . $attribute->name . '" attribute of ' . self::article($resource->type) . ' "' . $resource->type . '" resource', $members, $doc, $file, [$const]);
    }

    /**
     * A value class with a readonly property per member, required members first, and a static
     * `_from()` that narrows a decoded object member by member: an optional member of the wrong type
     * is dropped and a required one throws.
     *
     * @param array<string, array{shape: Shape, required: bool}> $members
     * @param list<string>                                        $doc     the class docblock lines
     * @param list<string>                                        $before  members declared ahead of the constructor
     */
    public static function valueClass(string $class, string $subject, array $members, array $doc, string $file, array $before = []): ClassUnit
    {
        \uasort($members, static fn(array $a, array $b): int => (int) $b['required'] <=> (int) $a['required']);

        $guard = new Guard($subject, ['raw']);
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
            if (\in_array($memberShape->kind, [ShapeKind::Object, ShapeKind::List], true) || $memberShape->narrowed()) {
                $paramDocs[] = '@param ' . $typed->wireType() . ' $' . $property;
            }

            $in = $guard->variable($property);
            $out = $guard->variable($property . 'Value');
            $extract[] = '$' . $in . ' = $raw[' . Php::string($name) . '] ?? null;';
            $statements[] = '$' . $out . ' = null;';
            $statements = [...$statements, ...$guard->value($memberShape, $in, '$' . $out, $member['required'], null, $name), ''];
            $arguments[] = '$' . $out;
        }

        $body = $before;
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

    /** Whether a relation's linkage renders a `meta.pivot` with fields to read. */
    private static function readsPivot(RelationDescriptor $relation): bool
    {
        return $relation->pivot && $relation->pivotFields !== [];
    }

    /** `orderedTracksPivots` for a to-many, `ownerPivot` for a to-one. */
    private static function pivotAccessor(RelationDescriptor $relation): string
    {
        return Naming::member($relation->name) . ($relation->cardinality === Cardinality::Many ? 'Pivots' : 'Pivot');
    }

    /** The accessor on the resource base that wraps each linkage member's `meta.pivot`. */
    private static function pivotsAccessor(RelationDescriptor $relation, string $call, string $type): string
    {
        $class = Naming::perType($type, Naming::pascal($relation->name) . 'Pivot');
        $method = self::pivotAccessor($relation);
        $ref = Naming::member($relation->name) . 'Ref()';

        if ($relation->cardinality === Cardinality::One) {
            return Php::docblock(Php::wrap('The `meta.pivot` of the `' . $relation->name . '` linkage member, or null when the linkage is empty. Never throws: each field is checked when it is read.', 4), 4, false)
                . '    public function ' . $method . '(): ?' . $class . "\n    {\n"
                . '        $member = ' . $call . "->identifier();\n\n"
                . '        return $member === null ? null : new ' . $class . "(\$member);\n    }\n";
        }

        return Php::docblock([
            ...Php::wrap('The `meta.pivot` of each `' . $relation->name . '` linkage member, in linkage order, so the nth belongs to the nth member of `' . $ref . '`. Never throws: each field is checked when it is read.', 4),
            '',
            '@return list<' . $class . '>',
        ], 4, false)
            . '    public function ' . $method . "(): array\n    {\n"
            . "        \$pivots = [];\n\n"
            . '        foreach (' . $call . "->identifiers() as \$member) {\n"
            . '            $pivots[] = new ' . $class . "(\$member);\n"
            . "        }\n\n"
            . "        return \$pivots;\n    }\n";
    }

    /**
     * The read of one linkage member's `meta.pivot`, an accessor per field the response schema
     * renders. A field the server always renders reads strictly; any other is nullable, because a
     * pivot field the response leaves out is absent rather than excluded by a fieldset.
     */
    private function pivot(ApiDescriptor $api, ResourceDescriptor $resource, RelationDescriptor $relation, string $file, bool $hooks): ClassUnit
    {
        $type = $resource->type;
        $class = Naming::perType($type, Naming::pascal($relation->name) . 'Pivot');
        $imports = [self::RUNTIME . 'Resources\\Pivot'];

        $hooked = '';
        $methods = [];
        $properties = [];
        foreach ($relation->pivotFields as $field) {
            $attribute = new AttributeDescriptor(
                $field->name,
                $field->format,
                $field->enum,
                $field->nullable || !$field->required,
                $field->schema,
                $field->constraints,
            );
            $shape = $field->schema === null ? null : Shape::of($field->schema)->kind;
            $read = \in_array($shape, [ShapeKind::Object, ShapeKind::Tagged], true)
                ? new AttributeRead($field->name, Naming::member($field->name), $attribute->nullable ? '?array' : 'array', 'array<string, mixed>' . ($attribute->nullable ? '|null' : ''), ['return $this->' . ($attribute->nullable ? '_nullableMap' : '_map') . '(' . Php::string($field->name) . ');'])
                : $this->attribute($api, $resource, $attribute);
            $method = Naming::member($field->name);
            if (isset($properties[\strtolower($method)])) {
                throw new \LogicException(\sprintf('The pivot fields "%s" and "%s" of "%s.%s" would both generate %s().', $properties[\strtolower($method)], $field->name, $type, $relation->name, $method));
            }
            $properties[\strtolower($method)] = $field->name;

            $lines = $read->prose;
            if ($read->docType !== null) {
                $lines = [...$lines, ...($lines === [] ? [] : ['']), '@return ' . $read->docType];
            }
            $methods[] = ($lines === [] ? '' : Php::docblock($lines, 4, false))
                . '    public function ' . $method . '(): ' . $read->nativeType . "\n    {\n"
                . \implode("\n", Guard::indent($read->body, 8)) . "\n    }\n";
            $hooked .= ($read->docType === null ? '' : '    ' . Php::comment('@var ' . $read->docType) . "\n")
                . '    public ' . $read->nativeType . ' $' . $method . ' { get => $this->' . $method . "(); }\n";
            \array_push($imports, ...$read->imports);
        }
        $members = $hooks ? [$hooked, ...$methods] : $methods;

        $doc = [
            ...Php::wrap('The `meta.pivot` of one `' . $relation->name . '` linkage member of ' . self::article($type) . ' `' . $type . '` resource.'),
            '',
            ...Php::wrap('A field the server always renders reads as its type. Any other field reads as nullable, because a pivot takes no sparse fieldset and a field the response leaves out is simply absent.'),
            '',
            '@generated from the `meta.pivot` of `' . $type . '.' . $relation->name . '` linkage',
        ];

        $source = Php::docblock($doc, 0, false)
            . 'final class ' . $class . " extends Pivot\n{\n"
            . \implode("\n", $members)
            . "}\n";

        return new ClassUnit($class, UnitKind::FinalClass, $file, $source, \array_values(\array_unique($imports)));
    }

    /** `images` → `ImageLinks`, the typed declared links of the type. */
    private static function linksClass(string $type): string
    {
        return Naming::perType($type, 'Links');
    }

    /** `images`, `download` → `ImageDownloadLink`, the class of one declared link object. */
    private static function linkClass(string $type, string $link): string
    {
        return Naming::perType($type, Naming::pascal($link) . 'Link');
    }

    /**
     * The typed declared links of a type: the `<Type>Links` the resource base hands out, and for
     * each link object its class and, when it declares `meta` members, its meta class.
     *
     * @return list<ClassUnit>
     */
    private function declaredLinks(ApiDescriptor $api, ResourceDescriptor $resource, string $file, bool $hooks): array
    {
        $type = $resource->type;
        $class = self::linksClass($type);
        $out = [];

        $hooked = '';
        $methods = [];
        $claimed = [];
        foreach ($resource->links as $link) {
            $method = Naming::member($link->name);
            if (isset($claimed[\strtolower($method)])) {
                throw new \LogicException(\sprintf('The links "%s" and "%s" of "%s" would both generate %s().', $claimed[\strtolower($method)], $link->name, $type, $method));
            }
            $claimed[\strtolower($method)] = $link->name;

            $prose = Php::wrap($link->description ?? 'The `' . $link->name . '` link.', 4);
            if ($link->object) {
                $linkClass = self::linkClass($type, $link->name);
                $native = '?' . $linkClass;
                $body = 'return $this->_object(' . Php::string($link->name) . ', ' . $linkClass . '::class);';
                \array_push($out, ...$this->linkObject($api, $resource, $link, $file, $hooks));
            } else {
                $native = '?string';
                $body = 'return $this->_href(' . Php::string($link->name) . ');';
                $prose = [...$prose, '', ...Php::wrap('A bare link, read as its URL from either a string or a link object.', 4)];
            }
            $methods[] = Php::docblock($prose, 4, false)
                . '    public function ' . $method . '(): ' . $native . "\n    {\n        " . $body . "\n    }\n";
            $hooked .= '    public ' . $native . ' $' . $method . ' { get => $this->' . $method . "(); }\n";
        }

        $doc = [
            ...Php::wrap('The links ' . self::article($type) . ' `' . $type . '` resource declares, one typed accessor each.'),
            '',
            ...Php::wrap('Every declared link is optional, because the server leaves one out when it does not apply, so each accessor returns null for a link the response left out. A link the type does not declare is in the resource\'s `_links()`.'),
            '',
            '@generated from `components.schemas.' . self::schema($type) . 'Links`',
        ];
        \array_unshift($out, new ClassUnit(
            $class,
            UnitKind::FinalClass,
            $file,
            Php::docblock($doc, 0, false)
                . 'final class ' . $class . " extends DeclaredLinks\n{\n"
                . \implode("\n", $hooks ? [$hooked, ...$methods] : $methods)
                . "}\n",
            [self::RUNTIME . 'Resources\\DeclaredLinks'],
        ));

        return $out;
    }

    /**
     * One declared link object: its `href`, an accessor per static member, and `meta()` when it
     * declares `meta` members.
     *
     * @return list<ClassUnit>
     */
    private function linkObject(ApiDescriptor $api, ResourceDescriptor $resource, DeclaredLinkDescriptor $link, string $file, bool $hooks): array
    {
        $type = $resource->type;
        $class = self::linkClass($type, $link->name);
        $metaClass = $class . 'Meta';
        $out = [];

        $hooked = '    public string $href { get => $this->href(); }' . "\n";
        $methods = [];
        foreach ($link->members as $member) {
            $method = Naming::member($member->name);
            $native = $member->required ? 'string' : '?string';
            $prose = $member->const === null ? [] : ['Always `' . $member->const . '`.'];
            $methods[] = ($prose === [] ? '' : Php::docblock($prose, 4, false))
                . '    public function ' . $method . '(): ' . $native . "\n    {\n"
                . '        return $this->' . ($member->required ? '_string' : '_nullableString') . '(' . Php::string($member->name) . ");\n    }\n";
            $hooked .= '    public ' . $native . ' $' . $method . ' { get => $this->' . $method . "(); }\n";
        }
        if ($link->meta !== []) {
            $methods[] = Php::docblock(['The link\'s `meta`, each member typed.'], 4, false)
                . '    public function meta(): ' . $metaClass . "\n    {\n"
                . '        return $this->_metaAs(' . $metaClass . "::class);\n    }\n";
            $hooked .= '    public ' . $metaClass . ' $meta { get => $this->meta(); }' . "\n";
            $out[] = $this->linkMeta($api, $resource, $link, $file, $hooks);
        }

        $doc = [
            ...Php::wrap('The `' . $link->name . '` link of ' . self::article($type) . ' `' . $type . '` resource.' . ($link->description === null ? '' : ' ' . $link->description)),
            '',
            '@generated from `components.schemas.' . self::schema($type) . 'Links.properties.' . $link->name . '`',
        ];
        \array_unshift($out, new ClassUnit(
            $class,
            UnitKind::FinalClass,
            $file,
            Php::docblock($doc, 0, false)
                . 'final class ' . $class . " extends DeclaredLink\n{\n"
                . \implode("\n", $hooks ? [$hooked, ...$methods] : $methods)
                . "}\n",
            [self::RUNTIME . 'Resources\\DeclaredLink'],
        ));

        return $out;
    }

    /**
     * A declared link's `meta`, an accessor per member read the way an attribute is. A member a
     * request cannot hide reads strictly; any other is nullable, because the response leaves it
     * out.
     */
    private function linkMeta(ApiDescriptor $api, ResourceDescriptor $resource, DeclaredLinkDescriptor $link, string $file, bool $hooks): ClassUnit
    {
        $type = $resource->type;
        $class = self::linkClass($type, $link->name) . 'Meta';
        $imports = [self::RUNTIME . 'Resources\\LinkMeta'];

        $hooked = '';
        $methods = [];
        $claimed = [];
        foreach ($link->meta as $member) {
            $attribute = new AttributeDescriptor(
                $member->name,
                $member->format,
                $member->enum,
                $member->nullable || !$member->required,
                $member->schema,
                $member->constraints,
            );
            $shape = $member->schema === null ? null : Shape::of($member->schema)->kind;
            $read = \in_array($shape, [ShapeKind::Object, ShapeKind::Tagged], true)
                ? new AttributeRead($member->name, Naming::member($member->name), $attribute->nullable ? '?array' : 'array', 'array<string, mixed>' . ($attribute->nullable ? '|null' : ''), ['return $this->' . ($attribute->nullable ? '_nullableMap' : '_map') . '(' . Php::string($member->name) . ');'])
                : $this->attribute($api, $resource, $attribute);
            $method = Naming::member($member->name);
            if (isset($claimed[\strtolower($method)])) {
                throw new \LogicException(\sprintf('The meta members "%s" and "%s" of the "%s" link of "%s" would both generate %s().', $claimed[\strtolower($method)], $member->name, $link->name, $type, $method));
            }
            $claimed[\strtolower($method)] = $member->name;

            $lines = [...self::metaProse($member), ...$read->prose];
            if ($read->docType !== null) {
                $lines = [...$lines, ...($lines === [] ? [] : ['']), '@return ' . $read->docType];
            }
            $methods[] = ($lines === [] ? '' : Php::docblock($lines, 4, false))
                . '    public function ' . $method . '(): ' . $read->nativeType . "\n    {\n"
                . \implode("\n", Guard::indent($read->body, 8)) . "\n    }\n";
            $hooked .= ($read->docType === null ? '' : '    ' . Php::comment('@var ' . $read->docType) . "\n")
                . '    public ' . $read->nativeType . ' $' . $method . ' { get => $this->' . $method . "(); }\n";
            \array_push($imports, ...$read->imports);
        }

        $doc = [
            ...Php::wrap('The `meta` of the `' . $link->name . '` link of ' . self::article($type) . ' `' . $type . '` resource.'),
            '',
            ...Php::wrap('A member the server always renders reads as its type. Any other reads as nullable, because a request can hide it and the response then leaves it out.'),
            '',
            '@generated from `components.schemas.' . self::schema($type) . 'Links.properties.' . $link->name . '.properties.meta`',
        ];

        return new ClassUnit(
            $class,
            UnitKind::FinalClass,
            $file,
            Php::docblock($doc, 0, false)
                . 'final class ' . $class . " extends LinkMeta\n{\n"
                . \implode("\n", $hooks ? [$hooked, ...$methods] : $methods)
                . "}\n",
            \array_values(\array_unique($imports)),
        );
    }

    /**
     * A meta member's description, then its example as one line of JSON.
     *
     * @return list<string>
     */
    private static function metaProse(LinkMetaDescriptor $member): array
    {
        $lines = $member->description === null ? [] : Php::wrap($member->description, 4);
        if ($member->example !== null) {
            $example = \json_encode($member->example, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR);
            $lines = [...$lines, ...($lines === [] ? [] : ['']), 'Example: `' . $example . '`'];
        }

        return $lines;
    }

    public static function native(Shape $shape): string
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
        return new \LogicException(\sprintf('The "%s" attribute of %s is %s, which this codegen cannot generate a reader for yet.', $attribute->name, $resource->type, $what));
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
