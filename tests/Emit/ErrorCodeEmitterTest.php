<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiClient\Errors\Error;
use haddowg\JsonApiClient\Exceptions\CodedErrorResponse;
use haddowg\JsonApiClient\Exceptions\Conflict;
use haddowg\JsonApiClient\Exceptions\Forbidden;
use haddowg\JsonApiClient\Exceptions\JsonApiErrorResponse;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
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
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ErrorCodeEmitter::class)]
final class ErrorCodeEmitterTest extends TestCase
{
    private const string CATALOGUE = 'haddowg\\JsonApiCodegen\\Tests\\Scratch\\MusicCatalogCodes';

    public function testEveryCataloguedCodeIsEmittedCoresIncluded(): void
    {
        $errors = Fixtures::musicCatalogDescriptor()->errors;
        $source = Generated::file(FileKey::ERRORS);

        self::assertNotSame([], $errors);
        foreach ($errors as $error) {
            self::assertStringContainsString('final class ' . $error->class . ' extends ', $source, $error->code);
            self::assertStringContainsString('        ' . "'" . $error->code . "' => " . $error->class . "::class,\n", $source, $error->code);
        }
        self::assertStringContainsString('final class AccessDeniedError extends Forbidden implements CodedErrorResponse', $source);
    }

    public function testADocumentCataloguingNothingEmitsNoErrorsFile(): void
    {
        $api = new ApiDescriptor([], [], null, 24, []);

        self::assertSame([], [...(new ErrorCodeEmitter())->emit($api, Generated::config())]);
    }

    public function testACodeDispatchesThroughItsStatusClassAndTheMap(): void
    {
        $namespace = 'haddowg\\JsonApiCodegen\\Tests\\Scratch\\ApplicationCodes';
        $config = new GeneratorConfig($namespace, 'Shop', PhpTarget::Php83);
        $files = self::generate([
            'APPLICATION_ERROR' => new ErrorCodeDescriptor('APPLICATION_ERROR', 500, 'Error500', 'ApplicationError', 'Application error', null),
            'PAYMENT_REQUIRED' => new ErrorCodeDescriptor('PAYMENT_REQUIRED', 402, 'Error402PaymentRequired', 'PaymentRequiredError', 'Payment is required', null, operations: ['POST /orders']),
            'TRACK_LOCKED' => new ErrorCodeDescriptor(
                'TRACK_LOCKED',
                409,
                'Error409TrackLocked',
                'TrackLockedError',
                'Track is locked.',
                ErrorSourceKind::Pointer,
                'Another editor holds the lock on this track, so the write was refused. Wait for the lock to lapse, which takes at most five minutes, then send the request again. Matching */ on its own must not end the docblock.',
                'Track 42 is locked by ada@example.com until 14:05.',
                operations: ['DELETE /tracks/{id}', 'PATCH /tracks/{id}', 'POST /tracks/{id}/-actions/master'],
            ),
            'UPSTREAM_UNAVAILABLE' => new ErrorCodeDescriptor('UPSTREAM_UNAVAILABLE', 503, 'Error503', 'UpstreamUnavailableError', 'Upstream is unavailable', null),
        ], $config);

        self::assertSame([FileKey::ERRORS, FileKey::AUTOLOAD], \array_keys($files));
        $source = $files[FileKey::ERRORS];
        self::assertStringContainsString(
            " * `TRACK_LOCKED`: Track is locked.\n"
            . " *\n"
            . " * Another editor holds the lock on this track, so the write was refused. Wait for the lock to\n"
            . " * lapse, which takes at most five minutes, then send the request again. Matching *\\/ on its own\n"
            . " * must not end the docblock.\n"
            . " *\n"
            . " * Example detail: Track 42 is locked by ada@example.com until 14:05.\n"
            . " *\n"
            . " * Raised only by `DELETE /tracks/{id}`, `PATCH /tracks/{id}` and `POST\n"
            . " * /tracks/{id}/-actions/master`.\n"
            . " *\n"
            . " * @generated from `components.schemas.Error409TrackLocked`\n",
            $source,
        );
        self::assertStringContainsString(" * `PAYMENT_REQUIRED`: Payment is required.\n *\n * Raised only by `POST /orders`.\n *\n * @generated from", $source);
        self::assertStringContainsString(" * `APPLICATION_ERROR`: Application error.\n *\n * @generated from `components.schemas.Error500`", $source, 'a code without a description or example says only its title');
        self::assertStringContainsString("final class ShopErrorCodes\n", $source);
        self::assertStringNotContainsString('function meta()', $source, 'a code declaring no meta has no meta()');

        self::load($source);

        $map = self::map($namespace . '\\ShopErrorCodes');
        $expected = [
            'APPLICATION_ERROR' => ['ApplicationError', ServerError::class, 500],
            'PAYMENT_REQUIRED' => ['PaymentRequiredError', UnexpectedResponse::class, 402],
            'TRACK_LOCKED' => ['TrackLockedError', Conflict::class, 409],
            'UPSTREAM_UNAVAILABLE' => ['UpstreamUnavailableError', ServerError::class, 503],
        ];
        self::assertSame(\array_keys($expected), \array_keys($map));

        foreach ($expected as $code => [$name, $parent, $status]) {
            self::assertSame($code, \constant($map[$code] . '::CODE'));

            $exception = JsonApiErrorResponse::for($status, [new Error(code: $code)], codes: $map);
            self::assertSame($namespace . '\\' . $name, $exception::class, 'the runtime dispatches the code through the map');
            self::assertInstanceOf(CodedErrorResponse::class, $exception);
            self::assertInstanceOf($parent, $exception);
            self::assertSame($status, $exception->statusCode());
        }
    }

    public function testAHeaderSourceUsesNoSourceTrait(): void
    {
        $source = self::catalogue();

        self::assertStringContainsString(
            "final class MediaTypeUnsupportedError extends UnsupportedMediaType implements CodedErrorResponse\n{\n    public const string CODE = 'MEDIA_TYPE_UNSUPPORTED';\n",
            $source,
        );
        self::assertStringContainsString("final class FilterValueInvalidError extends BadRequest implements CodedErrorResponse\n{\n    use PointsAtQueryParameters;\n", $source);
        self::assertStringContainsString("final class AttributeValueInvalidError extends ValidationFailed implements CodedErrorResponse\n{\n    use PointsAtDocumentMembers;\n", $source);
    }

    public function testRequiredMetaIsReadIntoItsValueClass(): void
    {
        $source = self::catalogue();
        self::assertStringContainsString("    public function meta(): InsufficientScopeMeta\n", $source);

        $exception = JsonApiErrorResponse::for(403, [new Error(code: 'INSUFFICIENT_SCOPE', meta: [
            'requiredScopes' => ['articles:read', 'articles:write'],
            'missingScopes' => ['articles:write'],
            'include' => 7,
        ])], codes: self::catalogueMap());

        self::assertSame(self::CATALOGUE . '\\InsufficientScopeError', $exception::class);
        self::assertInstanceOf(Forbidden::class, $exception);
        $meta = self::call($exception, 'meta');
        self::assertIsObject($meta);
        self::assertSame(self::CATALOGUE . '\\InsufficientScopeMeta', $meta::class);
        self::assertSame(['requiredScopes' => ['articles:read', 'articles:write'], 'missingScopes' => ['articles:write'], 'include' => null], \get_object_vars($meta), 'an optional member of the wrong type is dropped');

        $this->expectException(MalformedDocument::class);
        $this->expectExceptionMessage('The "meta" of an error coded "INSUFFICIENT_SCOPE" has a "requiredScopes" member that is not a list.');

        self::call(JsonApiErrorResponse::for(403, [new Error(code: 'INSUFFICIENT_SCOPE')], codes: self::catalogueMap()), 'meta');
    }

    public function testOptionalMetaReadsAsNullWhenAbsent(): void
    {
        $source = self::catalogue();
        self::assertStringContainsString("    public function meta(): ?RequestBodyInvalidJsonMeta\n", $source);

        self::assertNull(self::call(JsonApiErrorResponse::for(400, [new Error(code: 'REQUEST_BODY_INVALID_JSON')], codes: self::catalogueMap()), 'meta'));

        $meta = self::call(JsonApiErrorResponse::for(400, [new Error(code: 'REQUEST_BODY_INVALID_JSON', meta: ['original' => '{"data":'])], codes: self::catalogueMap()), 'meta');
        self::assertIsObject($meta);
        self::assertSame(['original' => '{"data":'], \get_object_vars($meta));
    }

    public function testACodeClassNameClashingWithAnImportFails(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('SERVER is generated as ServerError');

        self::generate(['SERVER' => new ErrorCodeDescriptor('SERVER', 500, 'Error500', 'ServerError', 'Server is down', null)], Generated::config());
    }

    /**
     * The music catalog's `errors.php` in a namespace of its own, loaded once.
     */
    private static function catalogue(): string
    {
        static $source = null;
        if ($source === null) {
            $config = new GeneratorConfig(self::CATALOGUE, 'MusicCatalog', PhpTarget::Php83);
            $api = new ApiDescriptor([], [], null, 24, Fixtures::musicCatalogDescriptor()->errors);
            $source = Assembler::assemble((new ErrorCodeEmitter())->emit($api, $config), $config, 'x')[FileKey::ERRORS];
            self::load($source);
        }

        return $source;
    }

    /**
     * @return array<string, class-string<CodedErrorResponse&JsonApiErrorResponse>>
     */
    private static function catalogueMap(): array
    {
        self::catalogue();

        return self::map(self::CATALOGUE . '\\MusicCatalogErrorCodes');
    }

    /**
     * A generated code map, read by name.
     *
     * @return array<string, class-string<CodedErrorResponse&JsonApiErrorResponse>>
     */
    private static function map(string $class): array
    {
        $map = \constant($class . '::MAP');
        self::assertIsArray($map);

        $out = [];
        foreach ($map as $code => $coded) {
            if (!\is_string($coded) || !\is_a($coded, CodedErrorResponse::class, true) || !\is_a($coded, JsonApiErrorResponse::class, true)) {
                self::fail(\sprintf('%s maps %s to something that is not a coded error response.', $class, $code));
            }
            $out[(string) $code] = $coded;
        }

        return $out;
    }

    /** A generated method, called by name: the scratch namespace is not on this package's autoloader. */
    private static function call(object $object, string $method): mixed
    {
        $read = [$object, $method];
        self::assertIsCallable($read);

        return $read();
    }

    private static function load(string $source): void
    {
        $path = \sys_get_temp_dir() . '/json-api-codegen-errors-' . \getmypid() . '-' . \md5($source) . '.php';
        \file_put_contents($path, $source);
        require_once $path;
        \unlink($path);
    }

    /**
     * @param array<string, ErrorCodeDescriptor> $errors
     *
     * @return array<string, string>
     */
    private static function generate(array $errors, GeneratorConfig $config): array
    {
        $api = new ApiDescriptor([], [], null, 24, $errors);

        return Assembler::assemble((new ErrorCodeEmitter())->emit($api, $config), $config, 'x');
    }
}
