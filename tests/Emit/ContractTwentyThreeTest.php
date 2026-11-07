<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiClient\Exceptions\Codes\ErrorCodes;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Spec\ContractCompatibility;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * The attributes object and every object a field declares are closed, which the generated
 * client already assumes: a write shape lists only declared members and a read narrows only
 * them, so closing them changes nothing it emits. The server's refusal of an undeclared member
 * is the `ATTRIBUTE_MEMBER_UNRECOGNIZED` code, which the runtime ships, so nothing is generated for it.
 * Undiscriminated unions are covered by {@see UndiscriminatedUnionTest}.
 */
#[CoversClass(ContractCompatibility::class)]
#[CoversClass(DescriptorBuilder::class)]
final class ContractTwentyThreeTest extends TestCase
{
    public function testTheWitnessIsAContractThisCodegenReads(): void
    {
        $document = SpecDocument::fromJson(self::json(), 'the witness');

        self::assertSame(23, $document->contract());
        self::assertNull(ContractCompatibility::check($document->contract()));
    }

    public function testClosingTheObjectsChangesNothingTheClientEmits(): void
    {
        $document = \json_decode(self::json(), true, flags: \JSON_THROW_ON_ERROR);
        $open = static function (mixed $node) use (&$open): mixed {
            if (!\is_array($node)) {
                return $node;
            }
            if (($node['additionalProperties'] ?? null) === false) {
                unset($node['additionalProperties']);
            }

            return \array_map($open, $node);
        };

        self::assertSame(self::generate(self::json()), self::generate(\json_encode($open($document), \JSON_THROW_ON_ERROR)));
    }

    public function testTheUndeclaredMemberCodeComesFromTheRuntime(): void
    {
        self::assertArrayHasKey('ATTRIBUTE_MEMBER_UNRECOGNIZED', ErrorCodes::MAP);
        self::assertStringNotContainsString("'ATTRIBUTE_MEMBER_UNRECOGNIZED'", self::generate(self::json())['errors.php']);
    }

    /**
     * The generated files, under a fixed hash so two documents compare on what they generate.
     *
     * @return array<string, string>
     */
    private static function generate(string $json): array
    {
        return Generator::default()->generate(
            DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness')),
            new GeneratorConfig('Probe\\Closed', 'Closed', PhpTarget::Php83),
            'fixed',
        );
    }

    private static function json(): string
    {
        $json = \file_get_contents(Fixtures::path('contract-witness.openapi.json'));
        self::assertIsString($json);

        return $json;
    }
}
