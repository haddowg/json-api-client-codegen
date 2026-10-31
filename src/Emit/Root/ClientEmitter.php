<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Root;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Emitter;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;
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
 * `atomic()` is not generated. The runtime's generated-client docs gate it on the server
 * advertising the atomic `ext` media type, and the runtime has no transaction API for it to
 * return yet; until it does, an
 * `atomic()` here would have nothing to call.
 */
final class ClientEmitter implements Emitter
{
    private const string RUNTIME = 'haddowg\\JsonApiClient\\';

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

        $body = \implode('', $properties)
            . ($properties === [] ? '' : "\n")
            . "    public function __construct(ClientOptions \$options)\n"
            . "    {\n"
            . '        $fetcher = new Fetcher(new Transport(' . $options . "));\n"
            . ($assignments === [] ? '' : "\n" . \implode('', $assignments))
            . "    }\n";

        yield new ClassUnit(
            Naming::client($config->clientName),
            UnitKind::FinalClass,
            FileKey::CLIENT,
            Php::docblock($doc, 0, false) . 'final class ' . Naming::client($config->clientName) . "\n{\n" . $body . "}\n",
            [self::RUNTIME . 'ClientOptions', self::RUNTIME . 'Http\\Transport', self::RUNTIME . 'Resources\\Fetcher'],
        );
    }
}
