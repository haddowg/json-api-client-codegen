<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Root;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ErrorCodeDescriptor;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Emitter;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\UnitKind;

/**
 * A class per catalogued error code the runtime ships none for, and the map that lets the
 * runtime's dispatch find them, all in `errors.php`.
 *
 * Core's codes are not emitted: the runtime ships them (json-api-client ADR 0005). What is left is
 * the codes an application contributes and those a server newer than the installed runtime
 * already emits. Each class has the shape the runtime's own generator writes: it extends the class
 * its status maps to, carries `CODE`, carries `STATUS` and a constructor where the parent takes
 * the status as an argument, and uses the source trait its variant requires. When every code is
 * already covered nothing is emitted, so no `errors.php` is written.
 */
final class ErrorCodeEmitter implements Emitter
{
    private const string RUNTIME = 'haddowg\\JsonApiClient\\';

    /** The status classes the runtime exports with a fixed `STATUS` constant. */
    private const array PARENTS = [
        400 => 'BadRequest',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'NotFound',
        406 => 'NotAcceptable',
        409 => 'Conflict',
        415 => 'UnsupportedMediaType',
        422 => 'ValidationFailed',
        429 => 'TooManyRequests',
    ];

    private const array SOURCE_TRAITS = [
        'pointer' => 'PointsAtDocumentMembers',
        'parameter' => 'PointsAtQueryParameters',
    ];

    /**
     * @param list<string>|null $runtimeCodes the codes the runtime ships classes for; null reads them from the installed runtime
     */
    public function __construct(private readonly ?array $runtimeCodes = null) {}

    /** `MusicCatalog` → `MusicCatalogErrorCodes`: the per-API code map. */
    public static function mapClass(GeneratorConfig $config): string
    {
        return $config->clientName . 'ErrorCodes';
    }

    /**
     * The catalogued codes the runtime has no class for, sorted by code. The runtime is only
     * consulted when the document catalogues any code at all.
     *
     * @return list<ErrorCodeDescriptor>
     *
     * @throws RuntimeCatalogueUnavailable when the runtime's code map is needed and cannot be loaded
     */
    public function uncatalogued(ApiDescriptor $api): array
    {
        if ($api->errors === []) {
            return [];
        }

        $known = \array_flip($this->runtimeCodes ?? RuntimeCatalogue::codes());

        return \array_values(\array_filter($api->errors, static fn(ErrorCodeDescriptor $error): bool => !isset($known[$error->code])));
    }

    public function emit(ApiDescriptor $api, GeneratorConfig $config): iterable
    {
        $errors = $this->uncatalogued($api);
        if ($errors === []) {
            return;
        }

        $map = self::mapClass($config);
        foreach ($errors as $error) {
            yield self::codeClass($error, $map);
        }

        yield new ClassUnit(
            $map,
            UnitKind::FinalClass,
            FileKey::ERRORS,
            self::codeMap($errors, $map, $config),
            [self::RUNTIME . 'Exceptions\\CodedErrorResponse', self::RUNTIME . 'Exceptions\\JsonApiErrorResponse'],
        );
    }

    private static function codeClass(ErrorCodeDescriptor $error, string $map): ClassUnit
    {
        [$parent, $takesStatus] = match (true) {
            isset(self::PARENTS[$error->status]) => [self::PARENTS[$error->status], false],
            $error->status >= 500 => ['ServerError', true],
            default => ['UnexpectedResponse', true],
        };
        $trait = $error->source === null ? null : self::SOURCE_TRAITS[$error->source->value];

        $imports = [self::RUNTIME . 'Errors\\Error', self::RUNTIME . 'Exceptions\\' . $parent, self::RUNTIME . 'Exceptions\\CodedErrorResponse'];
        if ($trait !== null) {
            $imports[] = self::RUNTIME . 'Exceptions\\' . $trait;
        }

        $taken = \array_map(static fn(string $import): string => \substr($import, (int) \strrpos($import, '\\') + 1), $imports);
        if (\in_array($error->schema, [...$taken, $map], true)) {
            throw new \LogicException(\sprintf('The error code %s is generated as %s, a name the generated errors.php already uses for another class.', $error->code, $error->schema));
        }

        $body = [];
        if ($trait !== null) {
            $body[] = '    use ' . $trait . ";\n";
        }
        $body[] = '    public const string CODE = ' . Php::string($error->code) . ";\n";

        if ($takesStatus) {
            $body[] = '    public const int STATUS = ' . $error->status . ";\n";
            $body[] = Php::docblock(['@param list<Error> $errors'], 4, false)
                . "    public function __construct(array \$errors = [], ?string \$message = null, ?\\Throwable \$previous = null)\n"
                . "    {\n"
                . "        parent::__construct(self::STATUS, \$errors, \$message, \$previous);\n"
                . "    }\n";
        }

        $body[] = Php::docblock(['@param list<Error> $errors'], 4, false)
            . "    public static function fromErrors(array \$errors, ?string \$message = null, ?\\Throwable \$previous = null): static\n"
            . "    {\n"
            . "        return new self(\$errors, \$message, \$previous);\n"
            . "    }\n";

        $doc = [...Php::wrap('`' . $error->code . '`: ' . \rtrim($error->title, '.') . '.'), '', '@generated from `components.schemas.' . $error->schema . '`'];

        return new ClassUnit(
            $error->schema,
            UnitKind::FinalClass,
            FileKey::ERRORS,
            Php::docblock($doc, 0, false)
                . 'final class ' . $error->schema . ' extends ' . $parent . " implements CodedErrorResponse\n{\n"
                . \implode("\n", $body)
                . "}\n",
            $imports,
        );
    }

    /**
     * @param list<ErrorCodeDescriptor> $errors
     */
    private static function codeMap(array $errors, string $map, GeneratorConfig $config): string
    {
        $entries = '';
        foreach ($errors as $error) {
            $entries .= '        ' . Php::string($error->code) . ' => ' . $error->schema . "::class,\n";
        }

        $doc = [
            ...Php::wrap('Every error code this API catalogues that the runtime ships no class for, keyed by code. ' . Naming::client($config->clientName) . ' hands it to the runtime, which consults it before its own map.'),
            '',
            '@generated from the OpenAPI document\'s error catalogue',
        ];

        return Php::docblock($doc, 0, false)
            . 'final class ' . $map . "\n{\n"
            . Php::docblock(['@var array<string, class-string<CodedErrorResponse&JsonApiErrorResponse>>'], 4, false)
            . "    public const array MAP = [\n" . $entries . "    ];\n"
            . "}\n";
    }
}
