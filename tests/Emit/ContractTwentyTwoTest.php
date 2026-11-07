<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Root\ClientEmitter;
use haddowg\JsonApiCodegen\Emit\ScopeNote;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Whatever the server marks deprecated carries `@deprecated` on the members that read, write
 * or call it, so an IDE and PHPStan's deprecation rules flag every use: an attribute's accessor
 * and setters, a relation's accessors and setters, an operation's or action's method, a
 * filter's `where…()` and a sort family's `sortBy…()`. A deprecated sort key gets a sentence on
 * `sort()`, since a literal token carries no tag, and a type whose every endpoint is deprecated
 * gets the tag on its client property.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(Php::class)]
#[CoversClass(ScopeNote::class)]
#[CoversClass(ClientEmitter::class)]
final class ContractTwentyTwoTest extends TestCase
{
    public function testEveryMemberReadingWritingOrCallingSomethingDeprecatedIsTagged(): void
    {
        $articles = self::deprecatedMembers(self::generate(self::json())['resource-articles.php']);

        self::assertSame([
            'public function subtitle(): ?string',
            'public function formerAuthorRef(): ?Identifier',
            'public function hasFormerAuthor(): bool',
            'public function retract(): void',
            'public function related(): ?People',
            'public function subtitle(string $subtitle): self',
            'public function formerAuthor(Created|Identifier|People|null $formerAuthor): self',
            'public function whereState(string $value): static',
            'public function sortByLegacy(string $suffix, bool $descending = false): static',
            'public function subtitle(string $subtitle): self',
            'public function formerAuthor(Created|Identifier|People|null $formerAuthor): self',
        ], $articles);
    }

    public function testADeprecatedSortKeyIsNamedOnSort(): void
    {
        self::assertStringContainsString('The server marks the `length` sort', self::generate(self::json())['resource-articles.php']);
    }

    public function testATypeWhoseEveryEndpointIsDeprecatedIsTaggedOnTheClient(): void
    {
        self::assertStringContainsString(
            "    /** @deprecated The server marks every `tags` endpoint deprecated. */\n    public readonly Tags \$tags;\n",
            self::generate(self::json())['client.php'],
        );
    }

    public function testADocumentWithoutMarkersGeneratesNoDeprecation(): void
    {
        $document = \json_decode(self::json(), true, flags: \JSON_THROW_ON_ERROR);
        $strip = static function (mixed $node) use (&$strip): mixed {
            if (!\is_array($node)) {
                return $node;
            }
            unset($node['deprecated'], $node['x-deprecated-sorts']);

            return \array_map($strip, $node);
        };

        foreach (self::generate(\json_encode($strip($document), \JSON_THROW_ON_ERROR)) as $path => $source) {
            self::assertStringNotContainsString('@deprecated', $source, $path);
            self::assertStringNotContainsString('marks the', $source, $path);
        }
    }

    /**
     * The signature of each method or property whose docblock carries `@deprecated`, in order.
     *
     * @return list<string>
     */
    private static function deprecatedMembers(string $source): array
    {
        \preg_match_all('~\* @deprecated[^\n]*\n(?:\s*\*[^\n]*\n)*?\s*\*/\n\s*((?:public|private|protected)[^\n{]*?)\s*(?:\{|\n|;)~', $source, $matches);

        return \array_map('trim', $matches[1]);
    }

    /**
     * @return array<string, string>
     */
    private static function generate(string $json): array
    {
        return Generator::default()->generate(
            DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness')),
            new GeneratorConfig('Probe\\Deprecation', 'Deprecation', PhpTarget::Php83),
            \hash('sha256', $json),
        );
    }

    private static function json(): string
    {
        $json = \file_get_contents(Fixtures::path('contract-witness.openapi.json'));
        self::assertIsString($json);

        return $json;
    }
}
