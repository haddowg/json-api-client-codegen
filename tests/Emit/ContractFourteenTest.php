<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\DeclaredLinkDescriptor;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\DescriptorSerializer;
use haddowg\JsonApiCodegen\Descriptor\LinkMemberDescriptor;
use haddowg\JsonApiCodegen\Descriptor\LinkMetaDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\ReadModel\ReadModelEmitter;
use haddowg\JsonApiCodegen\Spec\ContractCompatibility;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Generator contract 14 lists the links a type declares as named properties of its
 * `<Type>ResourceLinks` (core ADR 0178), which from contract 18 holds them in the second entry of
 * an `allOf`. The witness's `images` declares a link object,
 * `download`, with two static members and three meta members, and a bare link, `thumbnail`.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(DescriptorSerializer::class)]
#[CoversClass(DeclaredLinkDescriptor::class)]
#[CoversClass(LinkMemberDescriptor::class)]
#[CoversClass(LinkMetaDescriptor::class)]
#[CoversClass(ReadModelEmitter::class)]
final class ContractFourteenTest extends TestCase
{
    private const array ARTICLE_LINKS = ['components', 'schemas', 'ArticlesResourceLinks', 'allOf', 1, 'properties'];

    private const array IMAGE_LINKS = ['components', 'schemas', 'ImagesResourceLinks', 'allOf', 1, 'properties'];

    public function testTheWitnessIsAContractThisCodegenReads(): void
    {
        $document = SpecDocument::fromJson(self::json(), 'the witness');

        self::assertSame(18, $document->contract());
        self::assertNull(ContractCompatibility::check($document->contract()));
    }

    public function testALinkObjectIsReadWithItsStaticMembersAndMeta(): void
    {
        $links = self::resource(self::json(), 'images')->links;
        self::assertSame(['download', 'thumbnail'], \array_keys($links));

        $download = $links['download'];
        self::assertTrue($download->object);
        self::assertSame('Where to fetch the original file.', $download->description);
        self::assertSame(['title', 'type'], \array_keys($download->members));
        self::assertSame('Original', $download->members['title']->const);
        self::assertTrue($download->members['title']->required);
        self::assertSame('image/png', $download->members['type']->const);

        self::assertSame(['method', 'expiresAt', 'headers'], \array_keys($download->meta));
        $expiresAt = $download->meta['expiresAt'];
        self::assertSame('date-time', $expiresAt->format);
        self::assertTrue($expiresAt->required);
        self::assertSame('When the URL stops working.', $expiresAt->description);
        self::assertSame('2026-10-26T12:00:00+00:00', $expiresAt->example);
        self::assertFalse($download->meta['headers']->required);
        self::assertTrue($download->meta['headers']->nullable);
        self::assertSame(['Range' => 'bytes=0-'], $download->meta['headers']->example);
    }

    public function testABareLinkIsReadAsOne(): void
    {
        $thumbnail = self::resource(self::json(), 'images')->links['thumbnail'];

        self::assertFalse($thumbnail->object);
        self::assertSame([], $thumbnail->members);
        self::assertSame([], $thumbnail->meta);
        self::assertSame('The `thumbnail` link, absent when it does not apply to the resource.', $thumbnail->description);
    }

    public function testSelfAndAnActionLinkAreNotDeclaredLinks(): void
    {
        self::assertSame([], self::resource(self::json(), 'articles')->links);
    }

    public function testAnActionLinkRenderedAsAStringIsStillTheActions(): void
    {
        // A server that opts out of link objects points every built link at LinkObject, as it
        // does a bare declared link, so the action's name is what keeps `publish` out.
        $json = self::patched([...self::ARTICLE_LINKS, 'publish'], ['allOf' => [['$ref' => '#/components/schemas/LinkObject']]]);

        self::assertSame([], self::resource($json, 'articles')->links);
    }

    public function testAnOlderDocumentDeclaresNoLinks(): void
    {
        $json = self::patched(['info', 'x-generator', 'contract'], 13);

        self::assertSame([], self::resource($json, 'images')->links);
        self::assertStringNotContainsString('_declaredLinks', self::generate($json)['resource-images.php']);
    }

    public function testADeclaredLinkOfAnUnknownShapeIsAnErrorByPointer(): void
    {
        $json = self::patched([...self::IMAGE_LINKS, 'download'], ['type' => 'object', 'properties' => ['url' => ['type' => 'string']]]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('components.schemas.ImagesResourceLinks.allOf.1.properties.download');

        self::resource($json, 'images');
    }

    public function testALinkObjectWithNoMetaGetsNoMetaClass(): void
    {
        $json = self::patched([...self::IMAGE_LINKS, 'download', 'properties', 'meta'], ['type' => 'object', 'properties' => []]);
        $source = self::generate($json)['resource-images.php'];

        self::assertStringContainsString('final class ImageDownloadLink extends DeclaredLink', $source);
        self::assertStringNotContainsString('ImageDownloadLinkMeta', $source);
    }

    public function testATypeWithoutDeclaredLinksGeneratesAsBefore(): void
    {
        $files = self::generate(self::json());

        self::assertStringNotContainsString('_declaredLinks', $files['resource-articles.php']);
        self::assertStringNotContainsString('DeclaredLink', $files['resource-articles.php']);
        self::assertStringContainsString('public function _declaredLinks(): ImageLinks', $files['resource-images.php']);
    }

    public function testThe83TargetHasTheSameAccessorsWithoutHooks(): void
    {
        $on83 = self::generate(self::json(), PhpTarget::Php83)['resource-images.php'];
        $on84 = self::generate(self::json())['resource-images.php'];

        foreach (['public function download(): ?ImageDownloadLink', 'public function thumbnail(): ?string', 'public function title(): string', 'public function meta(): ImageDownloadLinkMeta', 'public function expiresAt(): \DateTimeImmutable', 'public function headers(): ?array'] as $method) {
            self::assertStringContainsString($method, $on83);
            self::assertStringContainsString($method, $on84);
        }
        self::assertStringNotContainsString('public ?ImageDownloadLink $download { get => $this->download(); }', $on83);
        self::assertStringContainsString('public ?ImageDownloadLink $download { get => $this->download(); }', $on84);
    }

    public function testAMetaMemberCarriesItsDescriptionAndExample(): void
    {
        $source = self::generate(self::json())['resource-images.php'];

        self::assertStringContainsString("     * When the URL stops working.\n     *\n     * Example: `\"2026-10-26T12:00:00+00:00\"`\n     */\n    public function expiresAt(): \\DateTimeImmutable", $source);
        self::assertStringContainsString("     * Where to fetch the original file.\n     */\n    public function download(): ?ImageDownloadLink", $source);
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
     * The witness with the value at `$keys` replaced.
     *
     * @param list<int|string> $keys
     */
    private static function patched(array $keys, mixed $value): string
    {
        $document = \json_decode(self::json(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);
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
