<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiClient\Errors\Error;
use haddowg\JsonApiClient\Exceptions\CodedErrorResponse;
use haddowg\JsonApiClient\Exceptions\Conflict;
use haddowg\JsonApiClient\Exceptions\JsonApiErrorResponse;
use haddowg\JsonApiClient\Exceptions\ServerError;
use haddowg\JsonApiClient\Exceptions\UnexpectedResponse;
use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ErrorCodeDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ErrorSourceKind;
use haddowg\JsonApiCodegen\Emit\Assembler;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Root\ErrorCodeEmitter;
use haddowg\JsonApiCodegen\Emit\Root\RuntimeCatalogue;
use haddowg\JsonApiCodegen\Emit\Root\RuntimeCatalogueUnavailable;
use haddowg\JsonApiCodegen\Tests\Support\ArtefactParity;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ErrorCodeEmitter::class)]
#[CoversClass(RuntimeCatalogue::class)]
#[CoversClass(RuntimeCatalogueUnavailable::class)]
final class ErrorCodeEmitterTest extends TestCase
{
    private const string RUNTIME_CODES = __DIR__ . '/../../vendor/haddowg/json-api-client/src/Exceptions/Codes/';

    public function testTheMusicCatalogueNeedsNoErrorsFile(): void
    {
        self::assertNotSame([], Fixtures::musicCatalogDescriptor()->errors);
        self::assertSame([], (new ErrorCodeEmitter())->uncatalogued(Fixtures::musicCatalogDescriptor()));
        self::assertArrayNotHasKey(FileKey::ERRORS, Generated::musicCatalog());
    }

    public function testTheRuntimeCatalogueIsReadFromTheInstalledRuntime(): void
    {
        $codes = RuntimeCatalogue::codes();

        self::assertContains('ADDITION_PROHIBITED', $codes);
        self::assertSame(\count(\glob(self::RUNTIME_CODES . '*Error.php') ?: []), \count($codes));
    }

    public function testAMissingRuntimeStopsGenerationWithTheFix(): void
    {
        $this->expectException(RuntimeCatalogueUnavailable::class);
        $this->expectExceptionMessage('Install haddowg/json-api-client beside the codegen');

        RuntimeCatalogue::codes('haddowg\\JsonApiClient\\Missing\\ErrorCodes');
    }

    public function testADocumentCataloguingNothingDoesNotNeedTheRuntime(): void
    {
        $api = new ApiDescriptor([], [], null, 1, []);

        self::assertSame([], [...(new ErrorCodeEmitter())->emit($api, Generated::config())]);
    }

    /**
     * The runtime's own generator wrote these classes; a per-API code of the same shape must come
     * out token for token the same.
     *
     * @return iterable<string, array{ErrorCodeDescriptor}>
     */
    public static function runtimeShapes(): iterable
    {
        yield 'a 4xx with a pointer' => [new ErrorCodeDescriptor('ADDITION_PROHIBITED', 403, 'AdditionProhibitedError', 'Addition is prohibited', ErrorSourceKind::Pointer)];
        yield 'a 4xx with a parameter' => [new ErrorCodeDescriptor('QUERY_PARAM_MALFORMED', 400, 'QueryParamMalformedError', 'Query parameter is malformed', ErrorSourceKind::Parameter)];
        yield 'a 5xx' => [new ErrorCodeDescriptor('APPLICATION_ERROR', 500, 'ApplicationError', 'Application error', null)];
    }

    #[DataProvider('runtimeShapes')]
    public function testACodeClassHasTheRuntimesShape(ErrorCodeDescriptor $error): void
    {
        $files = self::generate([$error->code => $error], Generated::config());
        $runtime = \file_get_contents(self::RUNTIME_CODES . $error->schema . '.php');
        self::assertIsString($runtime);

        self::assertSame(
            ArtefactParity::normalised($runtime, $error->schema),
            ArtefactParity::normalised($files[FileKey::ERRORS], $error->schema),
        );
    }

    public function testApplicationCodesAreEmittedWithTheirMap(): void
    {
        $namespace = 'haddowg\\JsonApiCodegen\\Tests\\Scratch\\ApplicationCodes';
        $config = new GeneratorConfig($namespace, 'Shop', PhpTarget::Php83);
        $files = self::generate([
            'APPLICATION_ERROR' => new ErrorCodeDescriptor('APPLICATION_ERROR', 500, 'ApplicationError', 'Application error', null),
            'PAYMENT_REQUIRED' => new ErrorCodeDescriptor('PAYMENT_REQUIRED', 402, 'PaymentRequiredError', 'Payment is required', null),
            'TRACK_LOCKED' => new ErrorCodeDescriptor(
                'TRACK_LOCKED',
                409,
                'TrackLockedError',
                'Track is locked.',
                ErrorSourceKind::Pointer,
                'Another editor holds the lock on this track, so the write was refused. Wait for the lock to lapse, which takes at most five minutes, then send the request again. Matching */ on its own must not end the docblock.',
                'Track 42 is locked by ada@example.com until 14:05.',
            ),
            'UPSTREAM_UNAVAILABLE' => new ErrorCodeDescriptor('UPSTREAM_UNAVAILABLE', 503, 'UpstreamUnavailableError', 'Upstream is unavailable', null),
        ], $config, ['APPLICATION_ERROR']);

        self::assertSame([FileKey::ERRORS, FileKey::AUTOLOAD], \array_keys($files));
        $source = $files[FileKey::ERRORS];
        self::assertStringNotContainsString('class ApplicationError', $source, 'a code the runtime ships is not emitted');
        self::assertStringContainsString(
            " * `TRACK_LOCKED`: Track is locked.\n"
            . " *\n"
            . " * Another editor holds the lock on this track, so the write was refused. Wait for the lock to\n"
            . " * lapse, which takes at most five minutes, then send the request again. Matching *\\/ on its own\n"
            . " * must not end the docblock.\n"
            . " *\n"
            . " * Example detail: Track 42 is locked by ada@example.com until 14:05.\n"
            . " *\n"
            . " * @generated from `components.schemas.TrackLockedError`\n",
            $source,
        );
        self::assertStringContainsString(" * `PAYMENT_REQUIRED`: Payment is required.\n *\n * @generated from", $source, 'a code without a description or example renders as before');
        self::assertStringContainsString("final class ShopErrorCodes\n", $source);

        $path = \sys_get_temp_dir() . '/json-api-codegen-errors-' . \getmypid() . '.php';
        \file_put_contents($path, $source);
        require_once $path;
        \unlink($path);

        $map = \constant($namespace . '\\ShopErrorCodes::MAP');
        self::assertSame([
            'PAYMENT_REQUIRED' => $namespace . '\\PaymentRequiredError',
            'TRACK_LOCKED' => $namespace . '\\TrackLockedError',
            'UPSTREAM_UNAVAILABLE' => $namespace . '\\UpstreamUnavailableError',
        ], $map);

        $expected = [
            'PAYMENT_REQUIRED' => [UnexpectedResponse::class, 402],
            'TRACK_LOCKED' => [Conflict::class, 409],
            'UPSTREAM_UNAVAILABLE' => [ServerError::class, 503],
        ];
        foreach ($expected as $code => [$parent, $status]) {
            $class = $map[$code];
            self::assertTrue(\is_a($class, CodedErrorResponse::class, true));
            self::assertSame($code, \constant($class . '::CODE'));

            $exception = $class::fromErrors([new Error(code: $code)]);
            self::assertInstanceOf($parent, $exception);
            self::assertInstanceOf(JsonApiErrorResponse::class, $exception);
            self::assertSame($status, $exception->statusCode());
        }
    }

    public function testACodeClassNameClashingWithAnImportFails(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('SERVER_DOWN is generated as ServerError');

        self::generate(['SERVER_DOWN' => new ErrorCodeDescriptor('SERVER_DOWN', 500, 'ServerError', 'Server is down', null)], Generated::config());
    }

    /**
     * @param array<string, ErrorCodeDescriptor> $errors
     * @param list<string>                       $runtimeCodes
     *
     * @return array<string, string>
     */
    private static function generate(array $errors, GeneratorConfig $config, array $runtimeCodes = []): array
    {
        $api = new ApiDescriptor([], [], null, 2, $errors);

        return Assembler::assemble((new ErrorCodeEmitter($runtimeCodes))->emit($api, $config), $config, 'x');
    }
}
