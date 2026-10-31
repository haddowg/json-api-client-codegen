<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\DescriptorSerializer;
use haddowg\JsonApiCodegen\Descriptor\OperationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Descriptor\RelationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\RelationVerb;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\ReadModel\ReadModelEmitter;
use haddowg\JsonApiCodegen\Emit\ScopeNote;
use haddowg\JsonApiCodegen\Emit\Surface\RelationSource;
use haddowg\JsonApiCodegen\Spec\ContractCompatibility;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Generator contract 16 lists the OAuth scopes an operation needs in its `security` under the
 * `oauth2` scheme, and projects a polymorphic relation over one type as a
 * one-member union. The witness's articles need `articles:read` and
 * `articles:write`, its related reads add the related type's read scope, `publish` needs
 * `articles:publish` and the Atomic Operations endpoint `batch`. Its `subject` is a `MorphTo`
 * over `people` and its `gallery` a `MorphToMany` over `images`.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(DescriptorSerializer::class)]
#[CoversClass(OperationDescriptor::class)]
#[CoversClass(RelationDescriptor::class)]
#[CoversClass(ScopeNote::class)]
#[CoversClass(RelationSource::class)]
#[CoversClass(ReadModelEmitter::class)]
final class ContractSixteenTest extends TestCase
{
    public function testTheWitnessIsAContractThisCodegenReads(): void
    {
        $document = SpecDocument::fromJson(self::json(), 'the witness');

        self::assertSame(19, $document->contract());
        self::assertNull(ContractCompatibility::check($document->contract()));
    }

    public function testEachOperationCarriesTheScopesItsSecurityNames(): void
    {
        $articles = self::resource(self::json());

        self::assertSame(['articles:read'], $articles->operation(OperationKind::FetchMany)?->scopes);
        self::assertSame(['articles:read'], $articles->operation(OperationKind::FetchOne)?->scopes);
        self::assertSame(['articles:write'], $articles->operation(OperationKind::Create)?->scopes);
        self::assertSame(['articles:write'], $articles->operation(OperationKind::Update)?->scopes);
        self::assertSame(['articles:write'], $articles->operation(OperationKind::Delete)?->scopes);

        $author = $articles->relation('author');
        self::assertNotNull($author);
        self::assertSame(['articles:read', 'people:read'], $author->relatedRead?->scopes);
        self::assertSame(['articles:read', 'people:read'], $author->relationshipRead?->scopes);
        self::assertSame(['articles:write'], $author->mutation(RelationVerb::Set)?->scopes);

        self::assertSame(['articles:publish'], $articles->actions['publish']->scopes);
    }

    public function testTheAtomicEndpointCarriesItsOwnScopes(): void
    {
        $api = DescriptorBuilder::build(SpecDocument::fromJson(self::json(), 'the witness'));

        self::assertSame(['batch'], $api->atomic?->scopes);
    }

    public function testAnOperationNeedingNoScopeHasNone(): void
    {
        $api = DescriptorBuilder::build(SpecDocument::fromJson(self::json(), 'the witness'));

        // `security: []` declares fetchCollection.people public, and an action with no security
        // of its own inherits a document default that names no scope.
        self::assertNull($api->resource('people')?->operation(OperationKind::FetchMany)?->scopes);
        self::assertNull($api->resource('tags')?->operation(OperationKind::FetchOne)?->scopes);
        self::assertNull(self::resource(self::json())->actions['import']->scopes);
    }

    public function testScopesAreReadOnlyUnderAnOauth2Scheme(): void
    {
        $json = self::patched(['components', 'securitySchemes', 'oauth2'], ['type' => 'http', 'scheme' => 'bearer']);

        self::assertNull(self::resource($json)->operation(OperationKind::FetchMany)?->scopes);
    }

    public function testTheSchemeIsFoundByItsTypeNotItsName(): void
    {
        $json = self::patched(['components', 'securitySchemes', 'partnerAuth'], self::node(['components', 'securitySchemes', 'oauth2']));
        $json = self::patched(['components', 'securitySchemes', 'oauth2'], ['type' => 'http', 'scheme' => 'bearer'], $json);
        $json = self::patched(['paths', '/articles', 'get', 'security'], [['partnerAuth' => ['articles:read']]], $json);

        self::assertSame(['articles:read'], self::resource($json)->operation(OperationKind::FetchMany)?->scopes);
    }

    public function testTheDescriptorSerialisesScopesOnlyWherePresent(): void
    {
        $serialised = DescriptorSerializer::toArray(DescriptorBuilder::build(SpecDocument::fromJson(self::json(), 'the witness')));
        $resources = $serialised['resources'];
        self::assertIsArray($resources);
        self::assertIsArray($resources['articles']);
        self::assertIsArray($resources['articles']['operations']);
        self::assertIsArray($resources['articles']['operations']['fetchMany']);
        self::assertSame(['articles:read'], $resources['articles']['operations']['fetchMany']['scopes']);
        self::assertIsArray($resources['articles']['actions']);
        self::assertIsArray($resources['articles']['actions']['import']);
        self::assertArrayNotHasKey('scopes', $resources['articles']['actions']['import']);
        self::assertIsArray($serialised['atomic']);
        self::assertSame(['batch'], $serialised['atomic']['scopes']);
    }

    public function testAnOlderDocumentCarriesNoScopes(): void
    {
        $json = \json_encode(DescriptorSerializer::toArray(Fixtures::musicCatalogDescriptor()), \JSON_THROW_ON_ERROR);

        self::assertStringNotContainsString('"scopes"', $json);
    }

    public function testEachGeneratedMethodStatesTheScopesItNeeds(): void
    {
        $source = self::generate(self::json())['resource-articles.php'];

        self::assertStringContainsString(
            "     * Fetch the article, shaped by the optional projection: a closure over a fresh read projection,\n"
            . "     * which can also filter and sort an included relation, or a prebuilt read or write projection.\n"
            . "     *\n"
            . "     * Needs an access token granting the `articles:read` scope.\n"
            . "     *\n"
            . "     * @template TProj of object\n",
            $source,
        );
        self::assertStringContainsString(
            "     * The related resource itself, or null when the relationship is empty.\n"
            . "     *\n"
            . "     * Needs an access token granting the `articles:read` and `people:read` scopes.\n"
            . "     *\n"
            . "     * @throws",
            $source,
        );
        self::assertStringContainsString("     * Needs an access token granting the `articles:publish` scope.\n", $source);
        self::assertStringContainsString("     * Needs an access token granting the `articles:write` scope.\n     *\n     * @throws", $source);
    }

    public function testAMethodNeedingNoScopeSaysNothing(): void
    {
        $source = self::generate(self::json())['resource-tags.php'];

        self::assertStringNotContainsString('Needs an access token', self::between($source, 'final class TagHandle', "\n}\n"));
    }

    public function testASingleTypeMorphToIsPolymorphic(): void
    {
        $subject = self::resource(self::json())->relation('subject');

        self::assertNotNull($subject);
        self::assertSame(['people'], $subject->types);
        self::assertTrue($subject->polymorphic);
    }

    public function testASingleTypeMorphToManyIsPolymorphic(): void
    {
        $gallery = self::resource(self::json())->relation('gallery');

        self::assertNotNull($gallery);
        self::assertSame(['images'], $gallery->types);
        self::assertTrue($gallery->polymorphic);
    }

    public function testAMultiTypeRelationStaysPolymorphic(): void
    {
        $attachments = self::resource(self::json())->relation('attachments');

        self::assertNotNull($attachments);
        self::assertSame(['images', 'videos'], $attachments->types);
        self::assertTrue($attachments->polymorphic);
    }

    public function testANullableSingleTypeRelationIsNotPolymorphic(): void
    {
        $articles = self::resource(self::json());

        foreach (['author', 'cover', 'locked', 'tags', 'watchers'] as $name) {
            self::assertFalse($articles->writableRelation($name)?->polymorphic, $name);
        }
    }

    public function testTheMusicCatalogMarksOnlyItsMultiTypeRelationsPolymorphic(): void
    {
        $polymorphic = [];
        foreach (Fixtures::musicCatalogDescriptor()->resources as $resource) {
            foreach ($resource->relations as $relation) {
                if ($relation->polymorphic) {
                    $polymorphic[] = $resource->type . '.' . $relation->name;
                }
                self::assertSame(\count($relation->types) > 1, $relation->polymorphic, $resource->type . '.' . $relation->name);
            }
        }

        self::assertSame(['favorites.favoritable', 'libraries.items'], $polymorphic);
    }

    public function testASingleTypeMorphToCollapsedToARefIsNotPolymorphic(): void
    {
        // How a document before contract 16 projects the same relation.
        $json = self::patched(
            ['components', 'schemas', 'ArticlesSubjectRelationship', 'properties', 'data'],
            ['anyOf' => [['$ref' => '#/components/schemas/PeopleResourceIdentifier'], ['type' => 'null']]],
        );

        self::assertFalse(self::resource($json)->relation('subject')?->polymorphic);
    }

    public function testASingleTypeMorphToHydratesItsRelatedResourceByType(): void
    {
        $source = self::generate(self::json())['resource-articles.php'];
        $subject = self::between($source, 'final class ArticleSubjectRelation', "\n}\n");

        self::assertStringContainsString('return $raw === null ? null : self::member($raw, $context);', $subject);
        self::assertStringContainsString("'people' => People::_hydrate(\$raw, \$context),", $subject);
        self::assertStringContainsString('public function related(): ?People', $subject);

        $author = self::between($source, 'final class ArticleAuthorRelation', "\n}\n");
        self::assertStringContainsString('return $raw === null ? null : People::_hydrate($raw, $context);', $author);
        self::assertStringNotContainsString('private static function member(', $author);
    }

    public function testASingleTypeMorphToManyHydratesItsRelatedResourcesByType(): void
    {
        $gallery = self::between(self::generate(self::json())['resource-articles.php'], 'final class ArticleGalleryRelation', "\n}\n");

        self::assertStringContainsString('$gallery[] = self::member($raw, $context);', $gallery);
        self::assertStringContainsString("'images' => Image::_hydrate(\$raw, \$context),", $gallery);
    }

    public function testAnIncludedSingleTypeMorphToIsHydratedByType(): void
    {
        $json = self::patched(['paths', '/articles', 'get', 'parameters', self::includeIndex()], [
            'name' => 'include',
            'in' => 'query',
            'schema' => ['type' => 'array', 'items' => ['type' => 'string', 'enum' => ['author', 'tags', 'author.company', 'subject', 'gallery']]],
            'style' => 'form',
            'explode' => false,
        ]);
        $source = self::generate($json)['resource-articles.php'];

        self::assertStringContainsString(
            "        return match (\$raw['type'] ?? null) {\n"
            . "            People::TYPE => People::_hydrate(\$raw, \$context),\n"
            . "            default => throw MalformedDocument::unexpectedMember('The \"subject\" relationship', 'type', 'one of \"people\"'),\n",
            $source,
        );
        self::assertStringContainsString(
            "            \$gallery[] = match (\$raw['type'] ?? null) {\n"
            . "                Image::TYPE => Image::_hydrate(\$raw, \$context),\n",
            $source,
        );
        self::assertStringContainsString("return \$raw === null ? null : People::_hydrate(\$raw, \$this->_child('author'));", $source);
    }

    public function testASingleTypeMorphToTakesTheSameInputsAsBefore(): void
    {
        $source = self::generate(self::json())['resource-articles.php'];

        self::assertStringContainsString('@phpstan-type ArticleSubjectInput Created<PeopleBase>|Identifier|People|null', $source);
        self::assertStringContainsString('@phpstan-type ArticleGalleryInput list<Created<ImageBase>|Identifier|Image>', $source);
    }

    public function testTheEightPointThreeTargetGeneratesTheSameDispatch(): void
    {
        $source = self::generate(self::json(), PhpTarget::Php83)['resource-articles.php'];

        self::assertStringContainsString('return $raw === null ? null : self::member($raw, $context);', self::between($source, 'final class ArticleSubjectRelation', "\n}\n"));
    }

    /** The position of the articles collection read's `include` parameter. */
    private static function includeIndex(): string
    {
        $parameters = self::node(['paths', '/articles', 'get', 'parameters']);
        self::assertIsArray($parameters);
        foreach ($parameters as $index => $parameter) {
            if (\is_array($parameter) && ($parameter['name'] ?? null) === 'include') {
                return (string) $index;
            }
        }
        self::fail('The witness lists no include parameter on GET /articles.');
    }

    /**
     * The witness's value at `$keys`.
     *
     * @param list<string> $keys
     */
    private static function node(array $keys): mixed
    {
        $node = self::decoded();
        foreach ($keys as $key) {
            self::assertIsArray($node);
            $node = $node[$key] ?? null;
        }

        return $node;
    }

    private static function between(string $source, string $start, string $end): string
    {
        $from = \strpos($source, $start);
        self::assertIsInt($from);
        $to = \strpos($source, $end, $from);
        self::assertIsInt($to);

        return \substr($source, $from, $to - $from);
    }

    private static function resource(string $json): ResourceDescriptor
    {
        $resource = DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness'))->resource('articles');
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
     * @param list<string> $keys
     */
    private static function patched(array $keys, mixed $value, ?string $json = null): string
    {
        $document = \json_decode($json ?? self::json(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);
        $node = &$document;
        foreach ($keys as $key) {
            self::assertIsArray($node);
            $node = &$node[$key];
        }
        $node = $value;
        unset($node);

        return \json_encode($document, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
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
