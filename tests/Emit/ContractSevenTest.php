<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * What generator contracts 6 and 7 change in the generated client: nested read members the
 * server always renders are required, and an action body for a type the document doesn't
 * describe is an inline schema rather than a dangling `$ref`.
 *
 * The unmodified document's output is pinned by the golden files; the cases that change the
 * document change one thing in it.
 */
#[CoversClass(DescriptorBuilder::class)]
final class ContractSevenTest extends TestCase
{
    public function testANestedObjectsRenderedMembersAreRequiredOnRead(): void
    {
        $releases = Generated::file('resource-releases.php');

        self::assertStringContainsString('@phpstan-type ReleasePackagingShape array{material: non-empty-string, gatefold: bool|null}', $releases, 'required, and nullable where the schema says so');
        self::assertStringContainsString("@phpstan-type ReleaseFormatShape array{rpm: int<16, 78>, coloured: bool|null, medium: 'vinyl'}|array{discs: positive-int, medium: 'cd'}", $releases, 'each tagged-union branch has its own required');
        self::assertStringContainsString("    public function __construct(\n        public readonly int \$discs,\n    ) {}", $releases, 'a branch class takes a required member without a default');
        self::assertStringContainsString("throw MalformedDocument::unexpectedMember('The \"packaging\" attribute of a \"releases\" resource', 'material', 'a string');", $releases, 'the accessor throws when a required member is missing');
    }

    public function testTheWriteShapesKeepTheRequestBodysOwnRequired(): void
    {
        self::assertStringContainsString('packaging?: array{material: non-empty-string, gatefold?: bool}|null', Generated::file('resource-releases.php'));
    }

    public function testAMemberAHookRendersStaysOptional(): void
    {
        // `releaseInfo` is rendered by a hook, so the server lists none of its members.
        self::assertStringContainsString('@phpstan-type AlbumReleaseInfoShape array{label?: string|null, catalogueNumber?: string|null}', Generated::file('resource-albums.php'));
    }

    public function testAnOlderDocumentsNestedMembersStayOptional(): void
    {
        $releases = Generator::default()->generate(self::descriptor(static function (array $document): array {
            $document['info']['x-generator']['contract'] = 5;
            // Before contract 6 a nested read schema listed no members as required.
            unset($document['components']['schemas']['ReleasesAttributes']['properties']['packaging']['required']);

            return $document;
        }), Generated::config(), '')['resource-releases.php'];

        self::assertStringContainsString('@phpstan-type ReleasePackagingShape array{material?: non-empty-string, gatefold?: bool|null}', $releases);
    }

    public function testAnActionAnsweringWithATypeTheDocumentDoesNotDescribeNamesItsPointer(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected paths./albums/{id}/-actions/reissue.post.responses.200.content.application/vnd.api+json.schema to be a document of a type the document describes, as the response of the "reissue" action');

        self::descriptor(static function (array $document): array {
            $document['paths']['/albums/{id}/-actions/reissue']['post']['responses']['200']['content']['application/vnd.api+json']['schema'] = [
                'type' => 'object',
                'properties' => [
                    'data' => [
                        'type' => 'object',
                        'properties' => ['type' => ['type' => 'string', 'const' => 'reissue-jobs'], 'id' => ['type' => 'string']],
                        'required' => ['type', 'id'],
                    ],
                ],
                'required' => ['data'],
            ];

            return $document;
        });
    }

    public function testAnActionTakingAnOpenBodyNamesItsPointer(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected paths./albums/{id}/-actions/reissue.post.requestBody.content.application/vnd.api+json.schema to be a create document of a type the document describes, as the body of the "reissue" action');

        self::descriptor(static function (array $document): array {
            $document['paths']['/albums/{id}/-actions/reissue']['post']['requestBody']['content']['application/vnd.api+json']['schema'] = ['type' => 'object'];

            return $document;
        });
    }

    /**
     * @param \Closure(array<array-key, mixed>): array<array-key, mixed> $change
     */
    private static function descriptor(\Closure $change): ApiDescriptor
    {
        $json = \file_get_contents(Fixtures::path('music-catalog.openapi.json'));
        self::assertIsString($json);
        $document = \json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($document);

        return DescriptorBuilder::build(SpecDocument::fromDecoded($change($document)));
    }
}
