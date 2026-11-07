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
use haddowg\JsonApiCodegen\Emit\ReadModel\ReadModelEmitter;
use haddowg\JsonApiCodegen\Emit\ReadModel\Shape;
use haddowg\JsonApiCodegen\Emit\ReadModel\ShapeKind;
use haddowg\JsonApiCodegen\Emit\UnitKind;

/**
 * A class per catalogued error code, a value class per declared `meta`, and the map the
 * runtime's dispatch finds them through, all in `errors.php`.
 *
 * Every code is emitted, core's included: the runtime ships no code classes, so the document is
 * the one place they come from. Each class extends the class its status maps to, carries `CODE`,
 * carries `STATUS` and a constructor where the parent takes the status as an argument, and uses
 * the source trait its shape requires. Its docblock carries the code's title, description,
 * example detail and, for a code scoped to some operations, those operations. A code whose shape
 * declares `meta` gets `meta()`, reading the first error carrying the code into a value class
 * narrowed the way a structured attribute is. A document cataloguing no code gets no
 * `errors.php`.
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

    /** `MusicCatalog` → `MusicCatalogErrorCodes`: the per-API code map. */
    public static function mapClass(GeneratorConfig $config): string
    {
        return $config->clientName . 'ErrorCodes';
    }

    public function emit(ApiDescriptor $api, GeneratorConfig $config): iterable
    {
        if ($api->errors === []) {
            return;
        }

        $map = self::mapClass($config);
        foreach ($api->errors as $error) {
            $meta = self::metaClass($error);
            yield self::codeClass($error, $map, $meta);
            if ($meta !== null) {
                yield $meta;
            }
        }

        yield new ClassUnit(
            $map,
            UnitKind::FinalClass,
            FileKey::ERRORS,
            self::codeMap(\array_values($api->errors), $map, $config),
            [self::RUNTIME . 'Exceptions\\CodedErrorResponse', self::RUNTIME . 'Exceptions\\JsonApiErrorResponse'],
        );
    }

    /**
     * The value class a code's declared `meta` is read into, or null when it declares none, or
     * declares an object with no members to type.
     */
    private static function metaClass(ErrorCodeDescriptor $error): ?ClassUnit
    {
        if ($error->meta === null) {
            return null;
        }
        $shape = Shape::of($error->meta)->merged();
        if ($shape->kind !== ShapeKind::Object || $shape->members === []) {
            return null;
        }

        $doc = [
            ...Php::wrap('The `meta` an error coded `' . $error->code . '` carries, narrowed member by member: an optional member of the wrong type is dropped rather than trusted, and a required one throws.'),
            '',
            '@generated from `components.schemas.' . $error->schema . '`, its `meta`',
        ];

        return ReadModelEmitter::valueClass($error->metaClass(), 'The "meta" of an error coded "' . $error->code . '"', $shape->members, $doc, FileKey::ERRORS);
    }

    private static function codeClass(ErrorCodeDescriptor $error, string $map, ?ClassUnit $meta): ClassUnit
    {
        [$parent, $takesStatus] = match (true) {
            isset(self::PARENTS[$error->status]) => [self::PARENTS[$error->status], false],
            $error->status >= 500 => ['ServerError', true],
            default => ['UnexpectedResponse', true],
        };
        $trait = $error->source === null ? null : (self::SOURCE_TRAITS[$error->source->value] ?? null);

        $imports = [self::RUNTIME . 'Errors\\Error', self::RUNTIME . 'Exceptions\\' . $parent, self::RUNTIME . 'Exceptions\\CodedErrorResponse'];
        if ($trait !== null) {
            $imports[] = self::RUNTIME . 'Exceptions\\' . $trait;
        }
        $throws = $meta !== null && $meta->imports !== [];
        if ($throws) {
            $imports[] = self::RUNTIME . 'Exceptions\\MalformedDocument';
        }

        $taken = \array_map(static fn(string $import): string => \substr($import, (int) \strrpos($import, '\\') + 1), $imports);
        foreach (\array_filter([$error->class, $meta?->name]) as $name) {
            if (\in_array($name, [...$taken, $map], true)) {
                throw new \LogicException(\sprintf('The error code %s is generated as %s, a name the generated errors.php already uses for another class.', $error->code, $name));
            }
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

        if ($meta !== null) {
            $body[] = self::metaAccessor($error, $meta->name, $throws);
        }

        $doc = [...Php::wrap('`' . $error->code . '`: ' . \rtrim($error->title, '.') . '.'), ''];
        if ($error->description !== null) {
            $doc = [...$doc, ...Php::wrap($error->description), ''];
        }
        if ($error->exampleDetail !== null) {
            $doc = [...$doc, ...Php::wrap('Example detail: ' . $error->exampleDetail), ''];
        }
        if ($error->operations !== []) {
            $doc = [...$doc, ...Php::wrap('Raised only by ' . self::sentence(\array_map(static fn(string $operation): string => '`' . $operation . '`', $error->operations)) . '.'), ''];
        }
        $doc[] = '@generated from `components.schemas.' . $error->schema . '`';

        return new ClassUnit(
            $error->class,
            UnitKind::FinalClass,
            FileKey::ERRORS,
            Php::docblock($doc, 0, false)
                . 'final class ' . $error->class . ' extends ' . $parent . " implements CodedErrorResponse\n{\n"
                . \implode("\n", $body)
                . "}\n",
            $imports,
        );
    }

    /**
     * `meta()`: the first error carrying the code, its `meta` read into the code's value class.
     * Optional `meta` reads as null when the error carries none.
     */
    private static function metaAccessor(ErrorCodeDescriptor $error, string $class, bool $throws): string
    {
        $doc = Php::wrap(
            'The `meta` of the first error coded `' . $error->code . '`, narrowed to the members the code declares'
            . ($error->metaRequired ? '.' : ', or null when it carries none.'),
            4,
        );
        if ($throws) {
            $doc = [...$doc, '', '@throws MalformedDocument when a member the code requires is missing or of the wrong type'];
        }

        $body = $error->metaRequired
            ? '        return ' . $class . "::_from(\$this->withCode(self::CODE)->meta ?? []);\n"
            : "        \$meta = \$this->withCode(self::CODE)->meta ?? [];\n\n"
                . '        return $meta === [] ? null : ' . $class . "::_from(\$meta);\n";

        return Php::docblock($doc, 4, false)
            . '    public function meta(): ' . ($error->metaRequired ? '' : '?') . $class . "\n"
            . "    {\n"
            . $body
            . "    }\n";
    }

    /**
     * @param non-empty-list<string> $items
     */
    private static function sentence(array $items): string
    {
        $last = \array_pop($items);

        return $items === [] ? $last : \implode(', ', $items) . ' and ' . $last;
    }

    /**
     * @param list<ErrorCodeDescriptor> $errors
     */
    private static function codeMap(array $errors, string $map, GeneratorConfig $config): string
    {
        $entries = '';
        foreach ($errors as $error) {
            $entries .= '        ' . Php::string($error->code) . ' => ' . $error->class . "::class,\n";
        }

        $doc = [
            ...Php::wrap('Every error code this API catalogues, keyed by code. ' . Naming::client($config->clientName) . ' hands it to the runtime, which builds an error response whose errors share one of these codes as that code\'s class.'),
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
