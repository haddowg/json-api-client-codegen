<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ActionDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ActionSafety;
use haddowg\JsonApiCodegen\Descriptor\DescriptorBuilder;
use haddowg\JsonApiCodegen\Descriptor\DescriptorSerializer;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Emit\Generator;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Surface\ActionSource;
use haddowg\JsonApiCodegen\Spec\ContractCompatibility;
use haddowg\JsonApiCodegen\Spec\SpecDocument;
use haddowg\JsonApiCodegen\Spec\SpecException;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Generator contract 15 gives each method of a multi-method action its own operation id and puts
 * an action's declared safety on its operation as `x-safety` (core ADR 0179). The witness's
 * collection-scoped `export` answers to GET and POST, `preview` declares all three flags and
 * `purge` declares only `destructive`, beside an `x-workflows` extension of its own.
 */
#[CoversClass(DescriptorBuilder::class)]
#[CoversClass(DescriptorSerializer::class)]
#[CoversClass(ActionDescriptor::class)]
#[CoversClass(ActionSafety::class)]
#[CoversClass(ActionSource::class)]
final class ContractFifteenTest extends TestCase
{
    public function testTheWitnessIsAContractThisCodegenReads(): void
    {
        $document = SpecDocument::fromJson(self::json(), 'the witness');

        self::assertSame(15, $document->contract());
        self::assertNull(ContractCompatibility::check($document->contract()));
    }

    public function testEachMethodOfAMultiMethodActionIsItsOwnAction(): void
    {
        $actions = self::resource(self::json())->actions;

        self::assertArrayNotHasKey('export', $actions);
        self::assertSame('GET', $actions['export.get']->method);
        self::assertSame('POST', $actions['export.post']->method);
        foreach (['export.get', 'export.post'] as $key) {
            self::assertSame('export', $actions[$key]->name);
            self::assertTrue($actions[$key]->perMethod);
            self::assertSame($key, $actions[$key]->memberName());
        }

        self::assertFalse($actions['stats']->perMethod);
        self::assertSame('stats', $actions['stats']->memberName());
    }

    public function testAMultiMethodActionGeneratesOneMethodPerHttpMethod(): void
    {
        $source = self::generate(self::json())['resource-articles.php'];

        self::assertStringContainsString("Export: `GET /articles/-actions/export`. It declares no request body, so it takes no input.\n", $source);
        self::assertStringContainsString('public function exportGet(): array', $source);
        self::assertStringContainsString("return \$this->fetcher->meta(Request::get('/articles/-actions/export'));", $source);
        self::assertStringContainsString('public function exportPost(): array', $source);
        self::assertStringContainsString("return \$this->fetcher->meta(Request::post('/articles/-actions/export'));", $source);
        self::assertStringNotContainsString('public function export(', $source);
    }

    public function testASingleMethodActionKeepsItsName(): void
    {
        $source = self::generate(self::json())['resource-articles.php'];

        foreach (['stats', 'report', 'publish', 'draft', 'cover', 'bulkImport', 'preview', 'purge'] as $method) {
            self::assertStringContainsString('public function ' . $method . '(', $source);
        }
    }

    public function testDeclaredSafetyIsCarriedFlagByFlag(): void
    {
        $actions = self::resource(self::json())->actions;

        $preview = $actions['preview']->safety;
        self::assertNotNull($preview);
        self::assertSame(['readOnly' => true, 'destructive' => false, 'idempotent' => true], $preview->declared());

        $purge = $actions['purge']->safety;
        self::assertNotNull($purge);
        self::assertNull($purge->readOnly);
        self::assertTrue($purge->destructive);
        self::assertNull($purge->idempotent);
        self::assertSame(['destructive' => true], $purge->declared());
    }

    public function testAnActionThatDeclaresNoSafetyHasNone(): void
    {
        $actions = self::resource(self::json())->actions;

        foreach (['publish', 'stats', 'export.get', 'export.post'] as $key) {
            self::assertNull($actions[$key]->safety, $key);
        }
    }

    public function testTheDescriptorSerialisesOnlyTheDeclaredFlags(): void
    {
        $actions = self::serialised(self::json());

        self::assertSame(['readOnly' => true, 'destructive' => false, 'idempotent' => true], $actions['preview']['safety']);
        self::assertSame(['destructive' => true], $actions['purge']['safety']);
        self::assertArrayNotHasKey('safety', $actions['publish']);
        self::assertTrue($actions['export.get']['perMethod']);
        self::assertArrayNotHasKey('perMethod', $actions['publish']);
    }

    public function testTheDocblockStatesTheDeclaredSafety(): void
    {
        $source = self::generate(self::json())['resource-articles.php'];

        self::assertStringContainsString("     * Preview: `GET /articles/{id}/-actions/preview`. It declares no request body, so it takes no\n     * input. The server declares it read-only, not destructive and idempotent.\n", $source);
        self::assertStringContainsString("     * Purge: `POST /articles/{id}/-actions/purge`. It declares no request body, so it takes no\n     * input. The server declares it destructive.\n", $source);
        self::assertStringContainsString("     * Publish the article: `POST /articles/{id}/-actions/publish`. It declares no request body, so\n     * it takes no input.\n     *\n", $source);
    }

    public function testAnEmptySafetyObjectDeclaresNothing(): void
    {
        $actions = self::resource(self::patched(['paths', '/articles/{id}/-actions/purge', 'post', 'x-safety'], new \stdClass()))->actions;

        self::assertNull($actions['purge']->safety);
    }

    public function testAFlagThatIsNotABooleanIsAnErrorByPointer(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('x-safety.destructive');

        self::resource(self::patched(['paths', '/articles/{id}/-actions/purge', 'post', 'x-safety'], ['destructive' => 'yes']));
    }

    public function testAnOlderDocumentStillGeneratesThePreferredMethodUnderTheActionName(): void
    {
        // Before contract 15 every method of an action shared one operation id, and the codegen
        // generated only POST under the action's own name. A client generated then keeps it.
        $older = self::patched(['info', 'x-generator', 'contract'], 14);

        $actions = self::resource($older)->actions;
        self::assertArrayNotHasKey('export.get', $actions);
        self::assertSame('POST', $actions['export']->method);
        self::assertFalse($actions['export']->perMethod);
        self::assertArrayNotHasKey('perMethod', self::serialised($older)['export']);

        $source = self::generate($older)['resource-articles.php'];
        self::assertStringContainsString('public function export(): array', $source);
        self::assertStringNotContainsString('public function exportGet(', $source);
    }

    public function testThe83TargetGeneratesTheSameActions(): void
    {
        $on83 = self::generate(self::json(), PhpTarget::Php83)['resource-articles.php'];
        $on84 = self::generate(self::json())['resource-articles.php'];

        self::assertSame(self::method($on84, 'exportGet'), self::method($on83, 'exportGet'));
        self::assertSame(self::method($on84, 'preview'), self::method($on83, 'preview'));
    }

    /** One generated method, from its signature to its closing brace. */
    private static function method(string $source, string $name): string
    {
        $start = \strpos($source, '    public function ' . $name . '(');
        self::assertIsInt($start);
        $end = \strpos($source, "\n    }\n", $start);
        self::assertIsInt($end);

        return \substr($source, $start, $end - $start);
    }

    private static function resource(string $json): ResourceDescriptor
    {
        $resource = DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness'))->resource('articles');
        self::assertNotNull($resource);

        return $resource;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function serialised(string $json): array
    {
        $resources = DescriptorSerializer::toArray(DescriptorBuilder::build(SpecDocument::fromJson($json, 'the witness')))['resources'];
        self::assertIsArray($resources);
        self::assertIsArray($resources['articles']);
        $actions = $resources['articles']['actions'];
        self::assertIsArray($actions);

        /** @var array<string, array<string, mixed>> $actions */
        return $actions;
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
    private static function patched(array $keys, mixed $value): string
    {
        $document = \json_decode(self::json(), true, flags: \JSON_THROW_ON_ERROR);
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

    private static function json(): string
    {
        $json = \file_get_contents(Fixtures::path('contract-witness.openapi.json'));
        self::assertIsString($json);

        return $json;
    }
}
