<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\DescriptorSerializer;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Spec\ContractCompatibility;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Generator contract 18 shares one links component per context (core ADR 0188). A type with
 * named links points at a `<Type>ResourceLinks` that is an `allOf` of the shared `ResourceLinks`
 * and an object holding them, and every other type points at `ResourceLinks` itself. The
 * witness's `images` declares links and `articles` has action links, so both keep a component of
 * their own, while `people` points at the shared one.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(DescriptorSerializer::class)]
final class ContractEighteenTest extends TestCase
{
    private const array IMAGE_LINKS = ['components', 'schemas', 'ImagesResourceLinks'];

    public function testTheWitnessIsAContractThisCodegenReads(): void
    {
        $document = SpecDocument::fromJson(self::json(), 'the witness');

        self::assertSame(18, $document->contract());
        self::assertNull(ContractCompatibility::check($document->contract()));
    }

    public function testTheWitnessHasOnlyTheSharedLinksComponents(): void
    {
        $names = \array_keys(SpecDocument::fromJson(self::json(), 'the witness')->schemas());
        $links = \array_values(\array_filter($names, static fn(string $name): bool => \str_ends_with($name, 'Links')));
        \sort($links);

        self::assertSame([
            'ArticlesResourceLinks',
            'CollectionLinks',
            'DocumentLinks',
            'ImagesResourceLinks',
            'Links',
            'RelatedDocumentLinks',
            'RelationshipDocumentLinks',
            'RelationshipLinks',
            'ResourceLinks',
        ], $links);
    }

    public function testATypeOnTheSharedResourceLinksDeclaresNone(): void
    {
        $document = SpecDocument::fromJson(self::json(), 'the witness');
        self::assertSame('ResourceLinks', $document->refName($document->schema('PeopleResource')->get('properties')->get('links')));

        self::assertSame([], self::resource(self::json(), 'people')->links);
    }

    public function testTheNamedLinksAreReadFromTheAllOf(): void
    {
        $links = self::resource(self::json(), 'images')->links;

        self::assertSame(['download', 'thumbnail'], \array_keys($links));
        self::assertTrue($links['download']->object);
        self::assertSame(['title', 'type'], \array_keys($links['download']->members));
        self::assertSame(['method', 'expiresAt', 'headers'], \array_keys($links['download']->meta));
        self::assertFalse($links['thumbnail']->object);
    }

    public function testTheContractFourteenFormReadsTheSame(): void
    {
        // Contracts 14 to 17 listed the named links on the type's own component beside `self`.
        $document = \json_decode(self::json(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);
        $inline = self::at($document, [...self::IMAGE_LINKS, 'allOf', 1, 'properties']);
        self::assertIsArray($inline);
        $flat = self::patched([...self::IMAGE_LINKS], [
            'type' => 'object',
            'properties' => ['self' => ['$ref' => '#/components/schemas/RenderedLink'], ...$inline],
        ], 17);

        $before = DescriptorSerializer::toArray(DescriptorBuilder::build(SpecDocument::fromJson($flat, 'the witness')));
        $after = DescriptorSerializer::toArray(DescriptorBuilder::build(SpecDocument::fromJson(self::json(), 'the witness')));

        self::assertEquals(self::at($after, ['resources', 'images', 'links']), self::at($before, ['resources', 'images', 'links']));
        self::assertSame(self::generate(self::json())['resource-images.php'], self::generate($flat)['resource-images.php']);
    }

    public function testTheGeneratedLinksAreTheSameOnBothTargets(): void
    {
        foreach ([PhpTarget::Php83, PhpTarget::Php84] as $target) {
            $source = self::generate(self::json(), $target)['resource-images.php'];

            self::assertStringContainsString('public function _declaredLinks(): ImageLinks', $source);
            self::assertStringContainsString('@generated from `components.schemas.ImagesResourceLinks.properties.download`', $source);
        }
    }

    public function testAnAllOfEntryThatIsNeitherResourceLinksNorAnObjectIsAnErrorByPointer(): void
    {
        $json = self::patched([...self::IMAGE_LINKS, 'allOf', 0], ['$ref' => '#/components/schemas/LinkObject']);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('components.schemas.ImagesResourceLinks.allOf.0');

        self::resource($json, 'images');
    }

    private static function resource(string $json, string $type): ResourceDescriptor
    {
        $resource = DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness'))->resource($type);
        self::assertNotNull($resource);

        return $resource;
    }

    /**
     * @return array<string, string>
     */
    private static function generate(string $json, PhpTarget $target = PhpTarget::Php84): array
    {
        return Generator::default()->generate(
            DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness')),
            new GeneratorConfig(ContractWitnessTest::NAMESPACE, 'Witness', $target),
            'x',
        );
    }

    /**
     * @param array<mixed>     $document
     * @param list<int|string> $keys
     */
    private static function at(array $document, array $keys): mixed
    {
        $node = $document;
        foreach ($keys as $key) {
            self::assertIsArray($node);
            self::assertArrayHasKey($key, $node);
            $node = $node[$key];
        }

        return $node;
    }

    /**
     * The witness with the value at `$keys` replaced, and its contract too when one is given.
     *
     * @param list<int|string> $keys
     */
    private static function patched(array $keys, mixed $value, ?int $contract = null): string
    {
        $document = \json_decode(self::json(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);
        if ($contract !== null) {
            self::assertIsArray($document['info']);
            self::assertIsArray($document['info']['x-generator']);
            $document['info']['x-generator']['contract'] = $contract;
        }
        $node = &$document;
        foreach ($keys as $key) {
            self::assertIsArray($node);
            self::assertArrayHasKey($key, $node);
            $node = &$node[$key];
        }
        $node = $value;
        unset($node);

        return \json_encode($document, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
    }

    private static function json(): string
    {
        $json = \file_get_contents(Fixtures::path('contract-witness.openapi.json'));
        self::assertIsString($json);

        return $json;
    }
}
