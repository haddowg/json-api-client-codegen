<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Surface;

use haddowg\JsonApiCodegen\Descriptor\ActionDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ActionInput;
use haddowg\JsonApiCodegen\Descriptor\ActionOutput;
use haddowg\JsonApiCodegen\Descriptor\ActionScope;
use haddowg\JsonApiCodegen\Descriptor\Cardinality;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\UnitKind;

/**
 * The custom actions of one scope (`AlbumActions` on the id handle, `AlbumCollectionActions` on
 * the type accessor), one method per action.
 *
 * Each signature follows the action's own declaration. No request body takes no parameter, a raw
 * body takes `StreamInterface|string` (nullable when the body is not required), and a JSON:API
 * body takes the create forms of its type. No response is `void`, a meta-only document is
 * `array<string, mixed>`, and a resource document is the resource, projected where the action
 * declares `include` or `fields`.
 */
final class ActionSource
{
    public static function unit(TypeSurface $type, ActionScope $scope): ?ClassUnit
    {
        $actions = \array_values(\array_filter($type->resource->actions, static fn(ActionDescriptor $action): bool => $action->scope === $scope));
        if ($actions === []) {
            return null;
        }

        $resource = $scope === ActionScope::Resource;
        $name = $type->named($resource ? 'Actions' : 'CollectionActions');
        $imports = new Imports();
        $fetcher = $imports->runtime('Resources\\Fetcher');
        $members = [$resource
            ? "    public function __construct(\n        private readonly " . $fetcher . " \$fetcher,\n        private readonly string \$id,\n    ) {}\n"
            : '    public function __construct(private readonly ' . $fetcher . " \$fetcher) {}\n"];

        $aliases = [];
        $methods = [];
        foreach ($actions as $action) {
            $method = Naming::member($action->name);
            if ($method === '' || isset($methods[\strtolower($method)]) || \strtolower($method) === '__construct') {
                throw new \LogicException(\sprintf('The action "%s" of "%s" cannot be the method %s(), which another member already is.', $action->name, $type->type(), $method));
            }
            $methods[\strtolower($method)] = true;
            if ($action->input === ActionInput::Document) {
                $aliases[] = TypeSurface::importAlias(self::inputType($type, $action), 'CreateShape');
            }
            $members[] = self::method($type, $action, $method, $imports);
        }

        $aliases = \array_values(\array_unique($aliases));
        \sort($aliases, \SORT_STRING);
        $paths = \array_map(static fn(ActionDescriptor $action): string => '`' . $action->method . ' ' . $action->path . '`', $actions);
        $doc = [
            ...Php::wrap(
                'The ' . ($resource ? 'resource' : 'collection') . '-scoped custom actions of `' . $type->type() . '`, '
                . ($resource ? 'reached through one ' . $type->label() . "'s handle" : 'reached through the type accessor')
                . '. Each signature is derived from the action\'s own request body and response.',
            ),
            '',
        ];
        if ($aliases !== []) {
            $doc = [...$doc, ...$aliases, ''];
        }
        $doc[] = '@generated from ' . \implode(' and ', $paths);

        return new ClassUnit($name, UnitKind::FinalClass, $type->file(), SurfaceEmitter::declaration($doc, $name, $members), $imports->all());
    }

    private static function method(TypeSurface $type, ActionDescriptor $action, string $method, Imports $imports): string
    {
        $imports->runtime('Http\\Request');
        $path = TypeSurface::path($action->path);
        $output = self::outputType($type, $action);
        $outputClass = $output === null ? null : Naming::resource($output);
        $outputBase = $output === null ? null : Naming::base($output);
        $projection = $output === null || $action->projection === null ? null : Naming::perType($output, 'Projection');
        $many = $action->outputCardinality === Cardinality::Many;

        $params = [];
        $signature = [];
        $body = '';
        $requestBody = null;
        $prose = [];

        switch ($action->input) {
            case ActionInput::None:
                $prose[] = 'It declares no request body, so it takes no input.';
                break;
            case ActionInput::Raw:
                $contentType = $action->contentType ?? 'application/octet-stream';
                $stream = $imports->add('Psr\\Http\\Message\\StreamInterface');
                $prose[] = 'The body goes over the wire verbatim as `' . $contentType . '`, while the response is still negotiated as JSON:API.'
                    . ($action->bodyRequired ? '' : ' The body is optional, so it may be left out.');
                $signature[] = $action->bodyRequired ? $stream . '|string $body' : $stream . '|string|null $body = null';
                break;
            case ActionInput::Document:
                $input = self::inputType($type, $action);
                $prose[] = 'The body is a create document of `' . $input . '`, in any of its three input forms.';
                $params[] = [TypeSurface::createDocType($input), '$input'];
                $signature[] = TypeSurface::createNativeType($input) . ' $input';
                $body .= TypeSurface::createInput($input) . "\n";
                $requestBody = '\\json_encode($document->toDocument(), \\JSON_THROW_ON_ERROR)';
                break;
        }

        if ($projection !== null) {
            \assert($outputBase !== null);
            $params[] = [TypeSurface::projectionParam($projection, $outputBase), '$projection'];
            $signature[] = 'callable|' . $projection . '|null $projection = null';
            $body .= '        $query = ' . $projection . "::resolve(\$projection);\n\n";
        }

        $uri = $projection === null ? $path : '$query->appendTo(' . $path . ')';
        $build = match (true) {
            $action->input === ActionInput::Raw && !$action->bodyRequired => '$request',
            $action->input === ActionInput::Raw => TypeSurface::request($action->method, $uri) . '->withRawBody($body, ' . Php::string($action->contentType ?? 'application/octet-stream') . ')',
            $requestBody !== null => TypeSurface::request($action->method, $uri, $requestBody, $output === null ? 8 : 12),
            default => TypeSurface::request($action->method, $uri),
        };
        if ($action->input === ActionInput::Raw && !$action->bodyRequired) {
            $body .= '        $request = ' . TypeSurface::request($action->method, $uri) . ";\n\n";
            $build = '$body === null ? $request : $request->withRawBody($body, ' . Php::string($action->contentType ?? 'application/octet-stream') . ')';
        }

        $doc = [...Php::wrap(self::humanise($action->name) . ': `' . $action->method . ' ' . $action->path . '`. ' . \implode(' ', $prose), 4), ''];
        if ($projection !== null) {
            $doc = [...$doc, '@template TProj of object', ''];
        }
        if ($params !== []) {
            $doc = [...$doc, ...TypeSurface::params($params), ''];
        }

        $reasons = [
            'Forbidden' => 'when the action is not permitted',
            'ValidationFailed' => $action->input === ActionInput::Document ? 'when the document fails validation' : 'when the payload fails validation',
        ];
        if ($action->scope === ActionScope::Resource) {
            $reasons['NotFound'] = 'when no ' . $type->label() . ' has this id';
        }

        switch ($action->output) {
            case ActionOutput::None:
                $return = 'void';
                $body .= $action->input === ActionInput::Raw && !$action->bodyRequired
                    ? "        \$this->fetcher->send(\n            " . $build . ",\n        );\n"
                    : '        $this->fetcher->send(' . $build . ");\n";
                $doc = [...$doc, ...Throws::tags($action->errorStatuses, $imports, $reasons)];
                break;
            case ActionOutput::Meta:
                $return = 'array';
                $body .= '        return $this->fetcher->meta(' . $build . ");\n";
                $doc = [...$doc, '@return array<string, mixed>', '', ...Throws::tags($action->errorStatuses, $imports, $reasons)];
                break;
            default:
                \assert($outputClass !== null && $outputBase !== null);
                $read = $projection === null
                    ? '        $context = $this->fetcher->read(' . $build . ");\n"
                    : "        \$context = \$this->fetcher->read(\n            " . $build . ",\n            \$query,\n        );\n\n";
                if ($many) {
                    $collection = $imports->runtime('Collections\\ResourceCollection');
                    $return = $collection;
                    $body .= $read . self::collect($outputClass, $outputBase, $projection !== null);
                    $doc = [...$doc, '@return ' . ($projection === null
                        ? $collection . '<' . $outputClass . '>'
                        : '($projection is null ? ' . $collection . '<' . $outputBase . '> : ' . $collection . '<' . $outputBase . '&TProj>)'), ''];
                    $doc = [...$doc, ...Throws::tags($action->errorStatuses, $imports, $reasons)];
                } else {
                    $imports->runtime('Exceptions\\MalformedDocument');
                    $return = $outputBase;
                    $body .= $read . TypeSurface::hydratePrimary($outputClass, $outputBase, $action->name, $projection !== null);
                    if ($projection !== null) {
                        $doc = [...$doc, '@return ($projection is null ? ' . $outputBase . ' : ' . $outputBase . '&TProj)', ''];
                    }
                    $doc = [...$doc, ...Throws::tags($action->errorStatuses, $imports, $reasons, true)];
                }
        }

        return Php::docblock($doc, 4, false)
            . SurfaceEmitter::signature($method, $signature, $return)
            . $body
            . "    }\n";
    }

    private static function collect(string $class, string $base, bool $projected): string
    {
        $item = $projected
            ? "            /** @var " . $base . "&TProj \$member */\n"
              . '            $member = $context->hydrate($raw, ' . $class . "::_hydrate(...));\n"
              . "            \$members[] = \$member;\n"
            : '            $members[] = ' . $class . "::_hydrate(\$raw, \$context);\n";

        return "        \$members = [];\n\n"
            . "        foreach (\$context->primaryList() as \$raw) {\n"
            . $item
            . "        }\n\n"
            . '        return new ResourceCollection($members, $context->document()->meta(), $context->document()->links());' . "\n";
    }

    /** The type whose create forms a JSON:API action body takes. */
    private static function inputType(TypeSurface $type, ActionDescriptor $action): string
    {
        $input = $action->inputType;
        $resource = $input === null ? null : $type->api->resource($input);
        if ($input === null || $resource === null || $resource->operation(OperationKind::Create) === null) {
            throw new \LogicException(\sprintf('The action "%s" of "%s" takes a JSON:API body of type "%s", which has no create forms to take it in.', $action->name, $type->type(), $input ?? 'unknown'));
        }

        return $input;
    }

    private static function outputType(TypeSurface $type, ActionDescriptor $action): ?string
    {
        if ($action->output !== ActionOutput::Document) {
            return null;
        }
        $output = $action->outputType;
        if ($output === null || $type->api->resource($output) === null) {
            throw new \LogicException(\sprintf('The action "%s" of "%s" answers with a resource of type "%s", which the document does not describe.', $action->name, $type->type(), $output ?? 'unknown'));
        }

        return $output;
    }

    /** `export-catalog` → `Export catalog`. */
    private static function humanise(string $name): string
    {
        return \ucfirst(\strtolower((string) \preg_replace('/(?<=[a-z0-9])(?=[A-Z])|[^A-Za-z0-9]+/', ' ', $name)));
    }
}
