<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\DescriptorSerializer;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Generator contract 19 writes every polymorphic linkage and related resource as a `oneOf` with a
 * `type` discriminator mapping each type to its member, and types each document's `included` as
 * such a `oneOf` over the resources its include paths reach.
 *
 * The music-catalog document writes its polymorphic unions that way already, which these tests
 * check by rewriting any union still written as an `anyOf` and finding none.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(DescriptorSerializer::class)]
final class ContractNineteenTest extends TestCase
{
    public function testTheMusicCatalogWritesEveryPolymorphicUnionDiscriminated(): void
    {
        $original = self::musicCatalog();

        self::assertSame($original, self::discriminated($original), 'no polymorphic union is left as an anyOf of resources');
    }

    public function testTheWitnessReadsItsPolymorphicRelationsFromTheirOneOf(): void
    {
        $articles = self::witness()->resource('articles');
        self::assertNotNull($articles);

        foreach (['subject' => ['people'], 'gallery' => ['images'], 'attachments' => ['images', 'videos'], 'sponsors' => ['people', 'images']] as $name => $types) {
            $relation = $articles->relation($name);
            self::assertNotNull($relation, $name);
            self::assertTrue($relation->polymorphic, $name);
            self::assertSame($types, $relation->types, $name);
        }

        self::assertFalse($articles->relation('author')?->polymorphic);
    }

    public function testEachOperationRecordsTheTypesItsIncludedCanCarry(): void
    {
        $articles = self::witness()->resource('articles');
        self::assertNotNull($articles);

        self::assertSame(['people', 'tags'], $articles->operation(OperationKind::FetchMany)?->projection?->included);
        self::assertSame(['people', 'tags'], $articles->operation(OperationKind::FetchOne)?->projection?->included);
    }

    public function testTheAtomicEndpointIsFoundWhateverShapeItsOperationsTake(): void
    {
        // The operation and result unions are split by kind and discriminated by type, which
        // nothing here reads: a batch is built from each type's write inputs.
        self::assertSame('/operations', self::witness()->atomic?->path);
    }

    public function testAMappingThatDisagreesWithItsMembersIsAnError(): void
    {
        $document = self::discriminated(self::musicCatalog());
        $components = $document['components'] ?? null;
        self::assertIsArray($components);
        $schemas = $components['schemas'] ?? null;
        self::assertIsArray($schemas);

        $pointer = null;
        foreach ($schemas as $name => $schema) {
            $items = \is_array($schema) && \str_ends_with((string) $name, 'Relationship') ? self::items($schema) : null;
            if ($items === null || !\is_array($items['discriminator'] ?? null)) {
                continue;
            }
            \assert(\is_array($schema) && \is_array($schema['properties']) && \is_array($schema['properties']['data']));
            $items['discriminator']['mapping'] = ['nobody' => '#/components/schemas/NobodyResourceIdentifier'];
            $schema['properties']['data']['items'] = $items;
            $schemas[$name] = $schema;
            $pointer = 'components.schemas.' . $name . '.properties.data.items.discriminator.mapping';
            break;
        }
        self::assertNotNull($pointer, 'the music catalog has a polymorphic to-many');
        $components['schemas'] = $schemas;
        $document['components'] = $components;

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected ' . $pointer . ' to map each member\'s type to that member');

        self::descriptor($document);
    }

    /**
     * A relationship component's to-many linkage items, or null for a to-one.
     *
     * @param array<array-key, mixed> $schema
     *
     * @return array<array-key, mixed>|null
     */
    private static function items(array $schema): ?array
    {
        $properties = $schema['properties'] ?? null;
        $data = \is_array($properties) ? ($properties['data'] ?? null) : null;
        $items = \is_array($data) ? ($data['items'] ?? null) : null;

        return \is_array($items) ? $items : null;
    }

    private static function witness(): ApiDescriptor
    {
        $json = \file_get_contents(Fixtures::witnessPath());
        self::assertIsString($json);

        return DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness'));
    }

    /** @return array<array-key, mixed> */
    private static function musicCatalog(): array
    {
        $json = \file_get_contents(Fixtures::musicCatalogPath());
        self::assertIsString($json);
        $document = \json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);

        return $document;
    }

    /**
     * The document with every `anyOf` that lists only resource or resource identifier `$ref`s
     * written as a `oneOf` discriminated by `type`, which is how contract 19 writes a polymorphic
     * union. A nullable union keeps its `anyOf`, as contract 19 does.
     *
     * @param array<array-key, mixed> $document
     *
     * @return array<array-key, mixed>
     */
    private static function discriminated(array $document): array
    {
        $components = $document['components'] ?? null;
        self::assertIsArray($components);
        $schemas = $components['schemas'] ?? null;
        self::assertIsArray($schemas);

        $rewrite = static function (mixed $node) use (&$rewrite, $schemas): mixed {
            if (!\is_array($node)) {
                return $node;
            }
            $node = \array_map($rewrite, $node);

            $branches = $node['anyOf'] ?? null;
            if (!\is_array($branches) || $branches === []) {
                return $node;
            }
            $mapping = [];
            foreach ($branches as $branch) {
                $ref = \is_array($branch) ? ($branch['$ref'] ?? null) : null;
                if (!\is_string($ref) || \preg_match('~^#/components/schemas/(\w+Resource(?:Identifier)?)$~', $ref, $match) !== 1) {
                    return $node;
                }
                $component = $schemas[$match[1]] ?? null;
                $properties = \is_array($component) ? ($component['properties'] ?? null) : null;
                $typeSchema = \is_array($properties) ? ($properties['type'] ?? null) : null;
                $type = \is_array($typeSchema) ? ($typeSchema['const'] ?? null) : null;
                if (!\is_string($type)) {
                    return $node;
                }
                $mapping[$type] = $ref;
            }
            unset($node['anyOf']);

            return [...$node, 'oneOf' => $branches, 'discriminator' => ['propertyName' => 'type', 'mapping' => $mapping]];
        };

        $rewritten = $rewrite($document);
        self::assertIsArray($rewritten);

        return $rewritten;
    }

    /** @param array<array-key, mixed> $document */
    private static function descriptor(array $document): ApiDescriptor
    {
        return DescriptorBuilder::build(SpecDocument::fromDecoded($document));
    }
}
