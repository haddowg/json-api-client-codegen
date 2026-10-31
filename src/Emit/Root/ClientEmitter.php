<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Root;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\AtomicDescriptor;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Emitter;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\ScopeNote;
use haddowg\JsonApiCodegen\Emit\Surface\SurfaceEmitter;
use haddowg\JsonApiCodegen\Emit\UnitKind;

/**
 * The root client in `client.php`: one readonly accessor per resource type, all built over one
 * transport.
 *
 * When the API catalogues error codes the runtime ships no class for, the client hands their map
 * to the transport's options, so a response carrying one of those codes is thrown as its class.
 * When it catalogues none, the client does not mention error codes at all.
 *
 * `atomic()` exists exactly when the document has an endpoint whose request body declares the
 * atomic `ext` media type, and runs a batch against that endpoint's path. Without one there is no
 * `atomic()` at all.
 */
final class ClientEmitter implements Emitter
{
    private const string RUNTIME = 'haddowg\\JsonApiClient\\';

    /**
     * What a batch can raise. The atomic endpoint is core's own and declares the same responses on
     * every server, so the list is fixed rather than read from the operation.
     */
    private const array ATOMIC_THROWS = [
        'BadRequest' => 'when the operations document is malformed',
        'Unauthorized' => 'when credentials are missing or invalid',
        'Forbidden' => 'when an operation is not permitted',
        'NotFound' => 'when an operation targets a resource that does not exist',
        'NotAcceptable' => 'when the negotiated media type cannot be satisfied',
        'Conflict' => 'when an operation conflicts with the resource state',
        'UnsupportedMediaType' => 'when the `Content-Type` is refused',
        'ValidationFailed' => 'when an operation fails validation',
        'ServerError' => 'when the server fails',
        'TransportException' => 'when no response is received at all',
        'MalformedDocument' => 'when the response does not carry one result per operation',
    ];

    public function __construct(private readonly ErrorCodeEmitter $errorCodes = new ErrorCodeEmitter()) {}

    public function emit(ApiDescriptor $api, GeneratorConfig $config): iterable
    {
        $properties = [];
        $assignments = [];
        $seen = [];

        foreach ($api->resources as $type => $resource) {
            if (!SurfaceEmitter::exposed($resource)) {
                continue;
            }
            $property = Naming::member($type);
            if (isset($seen[$property])) {
                throw new \LogicException(\sprintf('The types "%s" and "%s" would both be the client property $%s.', $seen[$property], $type, $property));
            }
            $seen[$property] = $type;

            $accessor = Naming::accessor($type);
            $properties[] = '    public readonly ' . $accessor . ' $' . $property . ";\n";
            $assignments[] = '        $this->' . $property . ' = new ' . $accessor . "(\$fetcher);\n";
        }

        $options = $this->errorCodes->uncatalogued($api) === []
            ? '$options'
            : '$options->withErrorCodes(' . ErrorCodeEmitter::mapClass($config) . '::MAP)';

        $doc = [
            ...Php::wrap('The API\'s root client: one accessor per resource type, each built over the same transport, so one client reaches the whole API.'),
            '',
            '@generated from the OpenAPI document\'s resource types',
        ];

        $atomic = $api->atomic;
        $imports = [self::RUNTIME . 'ClientOptions', self::RUNTIME . 'Http\\Transport', self::RUNTIME . 'Resources\\Fetcher'];

        if ($atomic === null) {
            $body = \implode('', $properties)
                . ($properties === [] ? '' : "\n")
                . "    public function __construct(ClientOptions \$options)\n"
                . "    {\n"
                . '        $fetcher = new Fetcher(new Transport(' . $options . "));\n"
                . ($assignments === [] ? '' : "\n" . \implode('', $assignments))
                . "    }\n";
        } else {
            $body = \implode('', $properties)
                . ($properties === [] ? '' : "\n")
                . "    private readonly Fetcher \$fetcher;\n\n"
                . "    public function __construct(ClientOptions \$options)\n"
                . "    {\n"
                . '        $this->fetcher = new Fetcher(new Transport(' . $options . "));\n"
                . ($assignments === [] ? '' : "\n" . \str_replace('($fetcher)', '($this->fetcher)', \implode('', $assignments)))
                . "    }\n"
                . "\n"
                . self::atomic($atomic);
            $imports[] = self::RUNTIME . 'Atomic\\Tx';
            foreach (self::ATOMIC_THROWS as $class => $when) {
                $imports[] = self::RUNTIME . 'Exceptions\\' . $class;
            }
            \sort($imports, \SORT_STRING);
        }

        yield new ClassUnit(
            Naming::client($config->clientName),
            UnitKind::FinalClass,
            FileKey::CLIENT,
            Php::docblock($doc, 0, false) . 'final class ' . Naming::client($config->clientName) . "\n{\n" . $body . "}\n",
            $imports,
        );
    }

    private static function atomic(AtomicDescriptor $atomic): string
    {
        $width = \max(\array_map('strlen', \array_keys(self::ATOMIC_THROWS)));
        $throws = [];
        foreach (self::ATOMIC_THROWS as $class => $when) {
            $throws[] = '@throws ' . \str_pad($class, $width) . ' ' . $when;
        }

        $doc = ScopeNote::insert([
            ...Php::wrap('Apply a batch of write operations all-or-nothing, through the Atomic Operations extension.', 4),
            '',
            ...Php::wrap('Record each write on the `Tx` the callback receives; nothing is sent until it returns, and then everything is sent as one request. What the callback returns is returned here, so return the handles whose results you want to read.', 4),
            '',
            '@template R',
            '',
            '@param callable(Tx): R $batch',
            '',
            '@return R',
            '',
            ...$throws,
        ], $atomic->scopes);

        return Php::docblock($doc, 4, false)
            . "    public function atomic(callable \$batch): mixed\n"
            . "    {\n"
            . '        return $this->fetcher->atomic(' . Php::string($atomic->path) . ", \$batch);\n"
            . "    }\n";
    }
}
