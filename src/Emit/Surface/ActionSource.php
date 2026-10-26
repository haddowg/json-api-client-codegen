<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Surface;

use haddowg\JsonApiCodegen\Descriptor\ActionDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ActionInput;
use haddowg\JsonApiCodegen\Descriptor\ActionOutput;
use haddowg\JsonApiCodegen\Descriptor\ActionScope;
use haddowg\JsonApiCodegen\Descriptor\Cardinality;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Descriptor\RawMediaDescriptor;
use haddowg\JsonApiCodegen\Descriptor\RawMediaKind;
use haddowg\JsonApiCodegen\Descriptor\ValueSchema;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\ReadModel\ReadModelEmitter;
use haddowg\JsonApiCodegen\Emit\ReadModel\Shape;
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
    private const string JSON_API = 'application/vnd.api+json';

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
        $raw = $action->responses !== [];
        $multi = $action->input === ActionInput::Raw && $action->requests !== [];
        $encodes = false;
        $formRequest = null;

        $params = [];
        $signature = [];
        $templates = [];
        $body = '';
        $requestBody = null;
        $prose = [];

        switch ($action->input) {
            case ActionInput::None:
                $prose[] = 'It declares no request body, so it takes no input.';
                break;
            case ActionInput::Raw:
                $lone = $multi && \count($action->requests) === 1 && $action->requests[0]->kind->isForm() ? $action->requests[0] : null;
                if ($lone !== null) {
                    $fields = self::formFields($lone, $imports);
                    $prose[] = 'The body is ' . ($lone->kind === RawMediaKind::Multipart ? 'a set of form fields and files' : 'a set of form fields') . ', encoded as `' . $lone->mediaType . '`.'
                        . ($action->bodyRequired ? '' : ' The body is optional, so it may be left out.');
                    $params[] = [$action->bodyRequired ? $fields : $fields . '|null', '$body'];
                    $signature[] = $action->bodyRequired ? 'array $body' : '?array $body = null';
                    $formRequest = '        $request = ' . TypeSurface::request($action->method, '%URI%') . ";\n\n"
                        . '        $request = ' . ($action->bodyRequired ? '' : '$body === null ? $request : ') . '$request->' . ($lone->kind === RawMediaKind::Multipart ? 'withMultipartBody' : 'withFormBody') . "(\$body);\n\n";
                    break;
                }
                $stream = $imports->add('Psr\\Http\\Message\\StreamInterface');
                if ($multi) {
                    [$template, $contentTypeDoc, $bodyDoc, $bodyNative, $check, $payload, $builds] = self::requestChoice($action, $stream, $imports);
                    if ($template !== null) {
                        $templates[] = $template;
                    }
                    $encodes = $payload !== '';
                    if ($builds) {
                        $formRequest = '        $request = ' . TypeSurface::request($action->method, '%URI%') . ";\n\n" . $payload;
                        $payload = '';
                    }
                    $choices = \array_map(static fn(RawMediaDescriptor $media): string => '`' . $media->mediaType . '`', $action->requests);
                    $how = match (true) {
                        $builds => 'as form fields for a form media type, JSON-encoded for a JSON one and verbatim otherwise,',
                        $encodes => 'verbatim, or JSON-encoded for a JSON media type,',
                        default => 'verbatim',
                    };
                    $prose[] = 'The body goes over the wire ' . $how . ' as `$contentType`, '
                        . (\count($choices) === 1 ? 'a media type within ' . $choices[0] : 'which is one of ' . self::list($choices, 'or')) . '.'
                        . ($action->bodyRequired ? '' : ' The body is optional, so it may be left out.');
                    $params[] = [$contentTypeDoc, '$contentType'];
                    $params[] = [$action->bodyRequired ? $bodyDoc : $bodyDoc . '|null', '$body'];
                    $signature[] = 'string $contentType';
                    $signature[] = $action->bodyRequired ? $bodyNative . ' $body' : $bodyNative . '|null $body = null';
                    $body .= $check . $payload;
                    break;
                }
                $contentType = $action->contentType ?? 'application/octet-stream';
                $prose[] = 'The body goes over the wire verbatim as `' . $contentType . '`' . ($raw ? '.' : ', while the response is still negotiated as JSON:API.')
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
        $rawBody = $multi ? ($encodes ? '$payload' : '$body') . ', $contentType' : '$body, ' . Php::string($action->contentType ?? 'application/octet-stream');
        $build = match (true) {
            $action->input === ActionInput::Raw && !$action->bodyRequired => '$request',
            $action->input === ActionInput::Raw => TypeSurface::request($action->method, $uri) . '->withRawBody(' . $rawBody . ')',
            $requestBody !== null => TypeSurface::request($action->method, $uri, $requestBody, $output === null ? 8 : 12),
            default => TypeSurface::request($action->method, $uri),
        };
        if ($formRequest !== null) {
            $body .= \str_replace('%URI%', $uri, $formRequest);
            $build = '$request';
        } elseif ($action->input === ActionInput::Raw && !$action->bodyRequired) {
            $body .= '        $request = ' . TypeSurface::request($action->method, $uri) . ";\n\n";
            $build = ($multi && $encodes ? '$payload' : '$body') . ' === null ? $request : $request->withRawBody(' . $rawBody . ')';
        }
        if ($raw) {
            $body .= $build === '$request' ? '' : '        $request = ' . $build . ";\n\n";
            $build = '$request';
        }

        $headline = $action->summary === null ? self::humanise($action->name) : \rtrim(\trim($action->summary), '.');
        $doc = [...Php::wrap($headline . ': `' . $action->method . ' ' . $action->path . '`. ' . \implode(' ', $prose), 4), ''];
        if ($action->description !== null) {
            $doc = [...$doc, ...Php::wrap(\trim($action->description), 4), ''];
        }
        if ($raw) {
            $doc = [...$doc, ...Php::wrap(self::rawProse($action), 4), ''];
        }
        if ($projection !== null) {
            $templates[] = '@template TProj of object';
        }
        if ($templates !== []) {
            $doc = [...$doc, ...$templates, ''];
        }

        $reasons = [
            'Forbidden' => 'when the action is not permitted',
            'ValidationFailed' => $action->input === ActionInput::Document ? 'when the document fails validation' : 'when the payload fails validation',
        ];
        if ($action->scope === ActionScope::Resource) {
            $reasons['NotFound'] = 'when no ' . $type->label() . ' has this id';
        }

        // The JSON:API response: its PHPDoc return (null where the native type says it all), its
        // native type, the statements that read it, and whether it can lack a primary resource.
        $jsonDoc = null;
        $malformed = false;
        switch ($action->output) {
            case ActionOutput::None:
                $return = 'void';
                $read = $action->input === ActionInput::Raw && !$action->bodyRequired && $build !== '$request'
                    ? "        \$this->fetcher->send(\n            " . $build . ",\n        );\n"
                    : '        $this->fetcher->send(' . $build . ");\n";
                break;
            case ActionOutput::Meta:
                $return = 'array';
                $jsonDoc = 'array<string, mixed>';
                $read = '        return $this->fetcher->meta(' . $build . ");\n";
                break;
            default:
                \assert($outputClass !== null && $outputBase !== null);
                $read = $projection === null
                    ? '        $context = $this->fetcher->read(' . $build . ");\n"
                    : "        \$context = \$this->fetcher->read(\n            " . $build . ",\n            \$query,\n        );\n\n";
                if ($many) {
                    $collection = $imports->runtime('Collections\\ResourceCollection');
                    $return = $collection;
                    $read .= self::collect($outputClass, $outputBase, $projection !== null);
                    $jsonDoc = $projection === null
                        ? $collection . '<' . $outputClass . '>'
                        : '($projection is null ? ' . $collection . '<' . $outputBase . '> : ' . $collection . '<' . $outputBase . '&TProj>)';
                } else {
                    $imports->runtime('Exceptions\\MalformedDocument');
                    $return = $outputBase;
                    $malformed = true;
                    $read .= TypeSurface::hydratePrimary($outputClass, $outputBase, $action->name, $projection !== null);
                    if ($projection !== null) {
                        $jsonDoc = '($projection is null ? ' . $outputBase . ' : ' . $outputBase . '&TProj)';
                    }
                }
        }

        if (!$raw) {
            $body .= $read;
            if ($params !== []) {
                $doc = [...$doc, ...TypeSurface::params($params), ''];
            }
            if ($jsonDoc !== null) {
                $doc = [...$doc, '@return ' . $jsonDoc, ''];
            }
            $doc = [...$doc, ...Throws::tags($action->errorStatuses, $imports, $reasons, $malformed)];

            return Php::docblock($doc, 4, false)
                . SurfaceEmitter::signature($method, $signature, $return)
                . $body
                . "    }\n";
        }

        // Each raw media type the caller can ask for, read as its kind says.
        $arms = [];
        $json = false;
        foreach ($action->responses as $media) {
            $arms[] = self::rawRead($type, $action, $media, $imports);
            $json = $json || $media->kind === RawMediaKind::Json;
        }
        if ($json) {
            $imports->runtime('Exceptions\\MalformedDocument');
            $reasons['MalformedDocument'] = $malformed
                ? 'when the response carries no primary resource, or a JSON body is not what its schema says'
                : 'when a JSON body is not what its schema says';
            $malformed = true;
        }

        $natives = [];
        if ($action->answersJsonApi()) {
            $choices = [self::JSON_API, ...\array_map(static fn(array $arm): string => $arm['media'], $arms)];
            $params[] = [\implode('|', \array_map(Php::docString(...), $choices)), '$accept the media type to ask for; JSON:API unless another is named'];
            $signature[] = 'string $accept = ' . Php::string(self::JSON_API);
            $natives[] = $return;
            $chain = $jsonDoc ?? $return;
            foreach (\array_reverse($arms) as $arm) {
                $chain = '($accept is ' . Php::docString($arm['media']) . ' ? ' . $arm['doc'] . ' : ' . $chain . ')';
            }
            foreach ($arms as $arm) {
                $body .= '        if ($accept === ' . Php::string($arm['media']) . ") {\n"
                    . '            return ' . $arm['read'] . ";\n"
                    . "        }\n\n";
            }
            $body .= $read;
        } elseif (\count($arms) === 1) {
            $chain = $arms[0]['doc'];
            $body .= '        return ' . $arms[0]['read'] . ";\n";
        } else {
            $last = \array_pop($arms);
            $choices = [...\array_map(static fn(array $arm): string => $arm['media'], $arms), $last['media']];
            $params[] = [\implode('|', \array_map(Php::docString(...), $choices)), '$accept the media type to ask for'];
            $signature[] = 'string $accept = ' . Php::string($choices[0]);
            $chain = $last['doc'];
            foreach (\array_reverse($arms) as $arm) {
                $chain = '($accept is ' . Php::docString($arm['media']) . ' ? ' . $arm['doc'] . ' : ' . $chain . ')';
            }
            foreach ($arms as $arm) {
                $body .= '        if ($accept === ' . Php::string($arm['media']) . ") {\n"
                    . '            return ' . $arm['read'] . ";\n"
                    . "        }\n\n";
            }
            $body .= '        return ' . $last['read'] . ";\n";
            $arms[] = $last;
        }
        foreach ($arms as $arm) {
            $natives[] = $arm['native'];
        }
        $natives = \array_values(\array_unique($natives));
        $return = \in_array('mixed', $natives, true) ? 'mixed' : \implode('|', $natives);

        if ($params !== []) {
            $doc = [...$doc, ...TypeSurface::params($params), ''];
        }
        if ($chain !== $return) {
            $doc = [...$doc, '@return ' . $chain, ''];
        }
        $doc = [...$doc, ...Throws::tags($action->errorStatuses, $imports, $reasons, $malformed)];

        return Php::docblock($doc, 4, false)
            . SurfaceEmitter::signature($method, $signature, $return)
            . $body
            . "    }\n";
    }

    /**
     * How one raw response media type is asked for and read: the expression, its PHPDoc and
     * native types.
     *
     * @return array{media: string, read: string, doc: string, native: string}
     */
    private static function rawRead(TypeSurface $type, ActionDescriptor $action, RawMediaDescriptor $media, Imports $imports): array
    {
        $request = '$request->withAccept(' . Php::string($media->mediaType) . ')';
        $class = RawBodies::className($type->api, $type->type(), $action, $media);

        return match (true) {
            $media->kind === RawMediaKind::Binary => ['media' => $media->mediaType, 'read' => '$this->fetcher->stream(' . $request . ')', 'doc' => $imports->add('Psr\\Http\\Message\\StreamInterface'), 'native' => 'StreamInterface'],
            $media->kind === RawMediaKind::Text => ['media' => $media->mediaType, 'read' => '$this->fetcher->text(' . $request . ')', 'doc' => 'string', 'native' => 'string'],
            $class !== null => ['media' => $media->mediaType, 'read' => $class . '::_from($this->fetcher->jsonObject(' . $request . '))', 'doc' => $class, 'native' => $class],
            default => ['media' => $media->mediaType, 'read' => '$this->fetcher->json(' . $request . ')', 'doc' => 'mixed', 'native' => 'mixed'],
        };
    }

    private static function rawProse(ActionDescriptor $action): string
    {
        $media = \array_map(static fn(RawMediaDescriptor $media): string => '`' . $media->mediaType . '`', $action->responses);

        return match (true) {
            $action->answersJsonApi() => 'It answers with JSON:API unless `$accept` asks for ' . self::list($media, 'or') . ' instead. An error is always a JSON:API document.',
            \count($media) === 1 => 'It answers as ' . $media[0] . '. An error is a JSON:API document.',
            default => 'It answers as whichever of ' . self::list($media, 'or') . ' `$accept` asks for, the first by default. An error is a JSON:API document.',
        };
    }

    /**
     * The template, PHPDoc and native types of an action that takes its body in one of several
     * media types, and the statements that check the media type and encode the body.
     *
     * The last member says whether those statements build `$request` themselves, which they do
     * when a form is among the choices, since the runtime encodes a form and sets its media type.
     *
     * @return array{string|null, string, string, string, string, string, bool}
     */
    private static function requestChoice(ActionDescriptor $action, string $stream, Imports $imports): array
    {
        $concrete = [];
        $ranges = [];
        foreach ($action->requests as $media) {
            [$doc, $native] = match ($media->kind) {
                RawMediaKind::Json => self::jsonBodyType($media),
                RawMediaKind::Text, RawMediaKind::Binary => [$stream . '|string', $stream . '|string'],
                RawMediaKind::Multipart, RawMediaKind::Form => [self::formFields($media, $imports), 'array'],
            };
            if ($media->wildcard()) {
                $ranges[] = ['media' => $media, 'doc' => $doc, 'native' => $native];
            } else {
                $concrete[] = ['media' => $media, 'doc' => $doc, 'native' => $native];
            }
        }

        $literals = \array_map(static fn(array $entry): string => Php::docString($entry['media']->mediaType), $concrete);
        $bound = \implode('|', [...$literals, ...($ranges === [] ? [] : ['non-empty-string'])]);

        $fallback = $ranges === []
            ? \array_pop($concrete)['doc'] ?? 'mixed'
            : \implode('|', \array_values(\array_unique(\explode('|', \implode('|', \array_map(static fn(array $entry): string => $entry['doc'], $ranges))))));
        $chain = $fallback;
        foreach (\array_reverse($concrete) as $entry) {
            $chain = '(TContentType is ' . Php::docString($entry['media']->mediaType) . ' ? ' . $entry['doc'] . ' : ' . $chain . ')';
        }

        $natives = [];
        foreach ($action->requests as $media) {
            foreach (\explode('|', match ($media->kind) {
                RawMediaKind::Json => self::jsonBodyType($media)[1],
                RawMediaKind::Text, RawMediaKind::Binary => $stream . '|string',
                RawMediaKind::Multipart, RawMediaKind::Form => 'array',
            }) as $native) {
                $natives[$native] = true;
            }
        }
        $order = [$stream => 0, 'string' => 1, 'array' => 2];
        $natives = \array_keys($natives);
        \usort($natives, static fn(string $a, string $b): int => [$order[$a] ?? 3, $a] <=> [$order[$b] ?? 3, $b]);
        $native = \in_array('mixed', $natives, true) ? 'mixed' : \implode('|', $natives);

        $exact = \array_values(\array_filter($action->requests, static fn(RawMediaDescriptor $media): bool => !$media->wildcard()));
        $prefixes = \array_values(\array_filter(\array_map(
            static fn(RawMediaDescriptor $media): string => \rtrim(\explode('*', $media->mediaType, 2)[0], '*'),
            \array_filter($action->requests, static fn(RawMediaDescriptor $media): bool => $media->wildcard()),
        )));
        $anything = \count($prefixes) < \count(\array_filter($action->requests, static fn(RawMediaDescriptor $media): bool => $media->wildcard()));

        $check = '';
        if (!$anything) {
            $conditions = [];
            if ($exact !== []) {
                $conditions[] = '!\\in_array($contentType, [' . \implode(', ', \array_map(static fn(RawMediaDescriptor $media): string => Php::string($media->mediaType), $exact)) . '], true)';
            }
            foreach ($prefixes as $prefix) {
                $conditions[] = '!\\str_starts_with($contentType, ' . Php::string($prefix) . ')';
            }
            $accepted = \implode(', ', \array_map(static fn(RawMediaDescriptor $media): string => $media->mediaType, $action->requests));
            $check = '        if (' . \implode(' && ', $conditions) . ") {\n"
                . '            throw new \\InvalidArgumentException(\\sprintf(' . Php::string('The "' . $action->name . '" action takes its body as ' . (\count($action->requests) === 1 ? '' : 'one of ') . $accepted . ', not %s.') . ", \$contentType));\n"
                . "        }\n\n";
        }

        $json = \array_values(\array_filter($action->requests, static fn(RawMediaDescriptor $media): bool => $media->kind === RawMediaKind::Json));
        $forms = \array_values(\array_filter($action->requests, static fn(RawMediaDescriptor $media): bool => $media->kind->isForm()));
        // A template only where there is a literal to key the body's type on.
        $template = $literals === [] ? null : '@template TContentType of ' . $bound;
        $contentTypeDoc = $literals === [] ? 'non-empty-string' : 'TContentType';
        if ($forms !== []) {
            return [$template, $contentTypeDoc, $chain, $native, $check, self::formChoice($action, $stream, $json, $forms, $imports), true];
        }
        if ($json === []) {
            // Every body is already a string or a stream, so it goes over the wire as it is.
            return [$template, $contentTypeDoc, $chain, $native, $check, '', false];
        }
        $arms = [];
        if (!$action->bodyRequired) {
            $arms[] = '            $body === null => null,';
        }
        $exactJson = \array_values(\array_filter($json, static fn(RawMediaDescriptor $media): bool => !$media->wildcard()));
        if ($exactJson !== []) {
            $arms[] = '            ' . \implode(', ', \array_map(static fn(RawMediaDescriptor $media): string => '$contentType === ' . Php::string($media->mediaType), $exactJson)) . ' => \\json_encode($body, \\JSON_THROW_ON_ERROR),';
        }
        if (\count($exactJson) < \count($json)) {
            $arms[] = "            \\str_ends_with(\\strtolower(\\explode(';', \$contentType, 2)[0]), 'json') => \\json_encode(\$body, \\JSON_THROW_ON_ERROR),";
        }
        $arms[] = '            $body instanceof ' . $stream . ', \\is_string($body) => $body,';
        $arms[] = '            default => throw new \\InvalidArgumentException(\\sprintf(' . Php::string('The "' . $action->name . '" action takes a %s body as a string or a stream.') . ', $contentType)),';
        $payload = "        \$payload = match (true) {\n" . \implode("\n", $arms) . "\n        };\n\n";

        return [
            $template,
            $contentTypeDoc,
            $chain,
            $native,
            $check,
            $payload,
            false,
        ];
    }

    /**
     * The statement that puts a body chosen by `$contentType` on `$request` when a form is among
     * the choices. A form goes to the runtime's encoder, which checks the fields and sets the
     * media type with its boundary; JSON is encoded; a string or a stream goes as it is.
     *
     * @param list<RawMediaDescriptor> $json
     * @param list<RawMediaDescriptor> $forms
     */
    private static function formChoice(ActionDescriptor $action, string $stream, array $json, array $forms, Imports $imports): string
    {
        $encoding = $imports->runtime('Http\\FormEncoding');
        $arms = [];
        if (!$action->bodyRequired) {
            $arms[] = '            $body === null => $request,';
        }
        foreach ($forms as $form) {
            $arms[] = '            $contentType === ' . Php::string($form->mediaType) . ' && \\is_array($body) => $request->'
                . ($form->kind === RawMediaKind::Multipart ? 'withMultipartBody(' . $encoding . '::multipartFields($body))' : 'withFormBody(' . $encoding . '::formFields($body))') . ',';
        }
        $exactJson = \array_values(\array_filter($json, static fn(RawMediaDescriptor $media): bool => !$media->wildcard()));
        if ($exactJson !== []) {
            $arms[] = '            ' . \implode(', ', \array_map(static fn(RawMediaDescriptor $media): string => '$contentType === ' . Php::string($media->mediaType), $exactJson)) . ' => $request->withRawBody(\\json_encode($body, \\JSON_THROW_ON_ERROR), $contentType),';
        }
        if (\count($exactJson) < \count($json)) {
            $arms[] = "            \\str_ends_with(\\strtolower(\\explode(';', \$contentType, 2)[0]), 'json') => \$request->withRawBody(\\json_encode(\$body, \\JSON_THROW_ON_ERROR), \$contentType),";
        }
        $arms[] = '            $body instanceof ' . $stream . ', \\is_string($body) => $request->withRawBody($body, $contentType),';
        $arms[] = '            default => throw new \\InvalidArgumentException(\\sprintf(' . Php::string('The "' . $action->name . '" action cannot send this body as %s.') . ', $contentType)),';

        return "        \$request = match (true) {\n" . \implode("\n", $arms) . "\n        };\n\n";
    }

    /**
     * The PHPDoc type of a form body: its fields, and for multipart its files. A schema listing
     * `properties` narrows it to an array shape, its required members required, sealed unless
     * `additionalProperties` lets other fields in.
     */
    private static function formFields(RawMediaDescriptor $media, Imports $imports): string
    {
        $file = $media->kind === RawMediaKind::Multipart ? $imports->runtime('Http\\FilePart') : null;
        $generic = 'string|int|float|bool' . ($file === null ? '' : '|' . $file);
        if ($media->schema === null) {
            return 'array<string, ' . $generic . '>';
        }

        $members = [];
        foreach ($media->schema->properties as $name => $property) {
            $members[] = Shape::key($name) . (\in_array($name, $media->schema->required, true) ? '' : '?') . ': ' . self::formValue($property, $file, $generic);
        }
        if ($media->additional !== false) {
            $members[] = '...<string, ' . ($media->additional === true ? $generic . '|list<' . $generic . '>' : self::formValue($media->additional, $file, $generic)) . '>';
        }

        return 'array{' . \implode(', ', $members) . '}';
    }

    /**
     * The PHP type of one form field: its scalar type, a file for a binary multipart member, a
     * list of either for an array, and any field value where the schema says nothing PHP can use.
     */
    private static function formValue(ValueSchema $schema, ?string $file, string $generic, bool $item = false): string
    {
        $types = \array_values(\array_diff($schema->types, ['null']));
        if ($schema->composed() || \count($types) !== 1) {
            return $generic;
        }

        return match ($types[0]) {
            'string' => match (true) {
                $schema->format === 'binary' => $file ?? 'string',
                $schema->enum !== [] => \implode('|', \array_map(Php::docString(...), $schema->enum)),
                default => 'string',
            },
            'integer' => 'int',
            'number' => 'int|float',
            'boolean' => 'bool',
            'array' => $item ? $generic : 'list<' . ($schema->items === null ? $generic : self::formValue($schema->items, $file, $generic, true)) . '>',
            default => $generic,
        };
    }

    /**
     * The PHPDoc and native type of a JSON request body: its schema's wire shape, or `mixed`
     * where the document gives none.
     *
     * @return array{string, string}
     */
    private static function jsonBodyType(RawMediaDescriptor $media): array
    {
        if ($media->schema === null) {
            return ['mixed', 'mixed'];
        }
        $shape = Shape::of($media->schema)->merged();

        return [$shape->wireType(), ReadModelEmitter::native($shape)];
    }

    /**
     * `a`, `a and b`, `a, b and c`.
     *
     * @param list<string> $items
     */
    private static function list(array $items, string $last = 'and'): string
    {
        $tail = \array_pop($items);

        return $items === [] ? (string) $tail : \implode(', ', $items) . ' ' . $last . ' ' . $tail;
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
