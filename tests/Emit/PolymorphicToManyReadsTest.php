<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\PaginatorKind;
use haddowg\JsonApiCodegen\Descriptor\RelationDescriptor;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Surface\RelationSource;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * A polymorphic to-many has no single related type whose collection could decide how its reads
 * page, so the endpoint's own `page` parameter decides: a `PaginatedCollection` of the member
 * union when one is advertised, a `ResourceCollection` of it when none is. The witness's
 * `gallery` is a one-type `MorphToMany` and its `attachments` a two-type one, and neither
 * endpoint advertises `page`, though `images` itself pages. Its `tags` is the monomorphic control.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(RelationDescriptor::class)]
#[CoversClass(RelationSource::class)]
final class PolymorphicToManyReadsTest extends TestCase
{
    public function testAnUnpagedPolymorphicToManyCarriesNoPaginator(): void
    {
        foreach (['gallery', 'attachments'] as $name) {
            $relation = self::relation(self::json(), $name);

            self::assertNotNull($relation->paginator, $name);
            self::assertSame(PaginatorKind::None, $relation->paginator->kind, $name);
        }
    }

    /**
     * @return iterable<string, array{PhpTarget}>
     */
    public static function targets(): iterable
    {
        yield '8.3' => [PhpTarget::Php83];
        yield '8.4' => [PhpTarget::Php84];
    }

    #[DataProvider('targets')]
    public function testAOneTypePolymorphicToManyReadsAnUnpagedCollection(PhpTarget $target): void
    {
        $gallery = self::handle(self::generate(self::json(), $target), 'ArticleGalleryRelation');

        self::assertStringContainsString("     * @return ResourceCollection<Image>\n", $gallery);
        self::assertStringContainsString('public function related(): ResourceCollection', $gallery);
        self::assertStringContainsString("     * @return ResourceCollection<Identifier>\n", $gallery);
        self::assertStringContainsString('public function get(): ResourceCollection', $gallery);
        self::assertStringContainsString('return new ResourceCollection($gallery, $document->meta(), $document->links());', $gallery);
        self::assertStringContainsString('$gallery[] = self::member($raw, $context);', $gallery);
        self::assertStringNotContainsString('PaginatedCollection', $gallery);
        self::assertStringNotContainsString('PageNumber', $gallery);
    }

    #[DataProvider('targets')]
    public function testAMultiTypePolymorphicToManyReadsAnUnpagedUnion(PhpTarget $target): void
    {
        $attachments = self::handle(self::generate(self::json(), $target), 'ArticleAttachmentsRelation');

        self::assertStringContainsString("     * @return ResourceCollection<Image|Video>\n", $attachments);
        self::assertStringContainsString('public function related(): ResourceCollection', $attachments);
        self::assertStringContainsString('private static function member(array $raw, ResourceContext $context): Image|Video', $attachments);
        self::assertStringNotContainsString('PaginatedCollection', $attachments);
    }

    #[DataProvider('targets')]
    public function testAMultiTypePolymorphicToManyWithAPageParameterReadsAPagedUnion(PhpTarget $target): void
    {
        $json = self::withPage('/articles/{id}/attachments', self::pageParameter('/articles/{id}/tags'));

        $paginator = self::relation($json, 'attachments')->paginator;
        self::assertNotNull($paginator);
        self::assertSame(PaginatorKind::Page, $paginator->kind);

        $attachments = self::handle(self::generate($json, $target), 'ArticleAttachmentsRelation');
        self::assertStringContainsString("     * @return PaginatedCollection<Image|Video, PageNumber>\n", $attachments);
        self::assertStringContainsString('public function related(): PaginatedCollection', $attachments);
        self::assertStringContainsString("     * @return PaginatedCollection<Identifier, PageNumber>\n", $attachments);
        self::assertStringContainsString(
            'fn(string $url): PaginatedCollection => $this->resources($this->fetcher->readUrl($url, new ReadQuery())),',
            $attachments,
        );
    }

    public function testAOneTypePolymorphicToManyKeepsAPageThatMatchesItsTypes(): void
    {
        // The same `page` as `/images` advertises, which a monomorphic relation would defer to.
        $json = self::withPage('/articles/{id}/gallery', self::pageParameter('/images'));

        $paginator = self::relation($json, 'gallery')->paginator;
        self::assertNotNull($paginator);
        self::assertSame(PaginatorKind::Page, $paginator->kind);

        $gallery = self::handle(self::generate($json, PhpTarget::Php84), 'ArticleGalleryRelation');
        self::assertStringContainsString("     * @return PaginatedCollection<Image, PageNumber>\n", $gallery);
    }

    public function testTheMusicCatalogsPagedPolymorphicToManyIsUnchanged(): void
    {
        $source = Generator::default()->generate(
            Fixtures::musicCatalogDescriptor(),
            new GeneratorConfig(ContractWitnessTest::NAMESPACE, 'Music', PhpTarget::Php84),
            'x',
        )['resource-libraries.php'];

        self::assertStringContainsString(
            "     * @return PaginatedCollection<Album|Artist|Track, PageNumber>\n",
            self::handle($source, 'LibraryItemsRelation'),
        );
    }

    #[DataProvider('targets')]
    public function testAMonomorphicToManyIsUnchanged(PhpTarget $target): void
    {
        $tags = self::handle(self::generate(self::json(), $target), 'ArticleTagsRelation');
        self::assertStringContainsString("     * @return PaginatedCollection<Tag, PageNumber>\n", $tags);
        self::assertStringContainsString('$tags[] = Tag::_hydrate($raw, $context);', $tags);
        self::assertStringNotContainsString('private static function member(', $tags);
    }

    private static function relation(string $json, string $name): RelationDescriptor
    {
        $relation = DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness'))->resource('articles')?->relation($name);
        self::assertNotNull($relation);

        return $relation;
    }

    /**
     * @return array<string, string>
     */
    private static function generate(string $json, PhpTarget $target): array
    {
        return Generator::default()->generate(
            DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness')),
            new GeneratorConfig(ContractWitnessTest::NAMESPACE, 'Witness', $target),
            'x',
        );
    }

    /**
     * @param array<string, string>|string $files
     */
    private static function handle(array|string $files, string $class): string
    {
        $source = \is_string($files) ? $files : ($files['resource-articles.php'] ?? '');
        $from = \strpos($source, 'final class ' . $class . "\n");
        self::assertIsInt($from, $class);
        $to = \strpos($source, "\n}\n", $from);
        self::assertIsInt($to);

        return \substr($source, $from, $to - $from);
    }

    /**
     * The `page` parameter a witness collection read advertises.
     *
     * @return array<string, mixed>
     */
    private static function pageParameter(string $path): array
    {
        foreach (self::parameters(self::decoded(), $path) as $parameter) {
            if (\is_array($parameter) && ($parameter['name'] ?? null) === 'page') {
                /** @var array<string, mixed> $parameter */
                return $parameter;
            }
        }
        self::fail(\sprintf('GET %s advertises no page parameter.', $path));
    }

    /**
     * The witness with `$page` appended to the parameters of `GET $path`.
     *
     * @param array<string, mixed> $page
     */
    private static function withPage(string $path, array $page): string
    {
        $document = self::decoded();
        $parameters = self::parameters($document, $path);
        $parameters[] = $page;
        $paths = $document['paths'];
        self::assertIsArray($paths);
        $item = $paths[$path];
        self::assertIsArray($item);
        $get = $item['get'];
        self::assertIsArray($get);
        $get['parameters'] = $parameters;
        $item['get'] = $get;
        $paths[$path] = $item;
        $document['paths'] = $paths;

        return \json_encode($document, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array<string, mixed> $document
     *
     * @return list<mixed>
     */
    private static function parameters(array $document, string $path): array
    {
        $paths = $document['paths'] ?? null;
        self::assertIsArray($paths);
        $item = $paths[$path] ?? null;
        self::assertIsArray($item);
        $get = $item['get'] ?? null;
        self::assertIsArray($get);
        $parameters = $get['parameters'] ?? [];
        self::assertIsArray($parameters);

        return \array_values($parameters);
    }

    /**
     * @return array<string, mixed>
     */
    private static function decoded(): array
    {
        $document = \json_decode(self::json(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);

        /** @var array<string, mixed> $document */
        return $document;
    }

    private static function json(): string
    {
        $json = \file_get_contents(Fixtures::path('contract-witness.openapi.json'));
        self::assertIsString($json);

        return $json;
    }
}
