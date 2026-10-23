<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Surface;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\OperationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Descriptor\PaginatorDescriptor;
use haddowg\JsonApiCodegen\Descriptor\PaginatorKind;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;

/**
 * One resource type as the operation surface sees it: its names, and the code fragments every
 * surface class writes the same way.
 *
 * The write forms and the projection classes belong to other emitters. This names them by the
 * rule those emitters follow: a type with a create operation has `<Type>Create`,
 * `<Type>CreateBuilder` and the `<Type>CreateShape` alias, one with an update has `<Type>Update`
 * and `<Type>UpdateShape`, and every type has `<Type>Projection`.
 */
final class TypeSurface
{
    /** The contract from which a write response's `include`/`fields` are declared (core #161). */
    private const int WRITE_PROJECTION_CONTRACT = 2;

    public function __construct(
        public readonly ApiDescriptor $api,
        public readonly ResourceDescriptor $resource,
    ) {}

    public function type(): string
    {
        return $this->resource->type;
    }

    public function file(): string
    {
        return FileKey::resource($this->resource->type);
    }

    /** `Album`. */
    public function concrete(): string
    {
        return Naming::resource($this->resource->type);
    }

    /** `AlbumBase`. */
    public function base(): string
    {
        return Naming::base($this->resource->type);
    }

    /** `Album` + `$suffix`. */
    public function named(string $suffix): string
    {
        return Naming::perType($this->resource->type, $suffix);
    }

    /** The type as a noun for prose: `public-profiles` → `public profile`. */
    public function label(): string
    {
        return \str_replace('-', ' ', Naming::singular($this->resource->type));
    }

    public function operation(OperationKind $kind): ?OperationDescriptor
    {
        return $this->resource->operation($kind);
    }

    /**
     * Whether a write's success response carries this type's resource document.
     *
     * From contract 2 a write declares `include`/`fields` exactly when its response is a resource
     * document of its type, so a write declaring neither (a `202` job) answers with something
     * else. Older documents declare neither on any write, and every write there returns the
     * resource.
     */
    public function returnsResource(OperationDescriptor $write): bool
    {
        return $write->projection !== null || ($this->api->contract ?? 0) < self::WRITE_PROJECTION_CONTRACT;
    }

    /** The concrete class a related type hydrates into, or null when the document does not describe that type. */
    public function classFor(string $type): ?string
    {
        return $this->api->resource($type) === null ? null : Naming::resource($type);
    }

    /**
     * The paginator a relation's reads use: its own where it differs, else the related type's.
     */
    public function relationPaginator(?PaginatorDescriptor $own, string $relatedType): PaginatorDescriptor
    {
        return $own ?? $this->api->resource($relatedType)->paginator ?? PaginatorDescriptor::none();
    }

    /**
     * The runtime page class a paginator reads into, or null when it does not paginate.
     *
     * A fixed-size paginator has its own page class, which carries no size: the server fixes it.
     * A menu reads into its default arm's page wherever nothing picks an arm, which is every
     * relation read.
     */
    public static function pageClass(PaginatorDescriptor $paginator): ?string
    {
        return match ($paginator->unchosen()->kind) {
            PaginatorKind::Page => 'PageNumber',
            PaginatorKind::Fixed => 'FixedPage',
            PaginatorKind::Offset => 'Offset',
            PaginatorKind::Cursor => 'Cursor',
            PaginatorKind::None => null,
            PaginatorKind::Multi => throw new \LogicException('A paginator menu has no default arm.'),
        };
    }

    /**
     * A path template as a PHP expression over the handle's `$this->id`:
     * `/albums/{id}/tracks` → `'/albums/' . $this->id . '/tracks'`.
     */
    public static function path(string $template): string
    {
        $parts = [];
        foreach (\explode('{id}', $template) as $index => $literal) {
            if ($index > 0) {
                $parts[] = '$this->id';
            }
            if ($literal !== '') {
                $parts[] = Php::string($literal);
            }
        }

        return \implode(' . ', $parts);
    }

    /** The docblock type of a projection argument over `$projection`'s builder. */
    public static function projectionParam(string $projection, string $base): string
    {
        return '(callable(' . $projection . '<' . $base . '>): ' . $projection . '<TProj>)|' . $projection . '<TProj>|null';
    }

    /** `ucfirst` for a sentence that opens with a generated label. */
    public static function sentence(string $text): string
    {
        return \ucfirst($text);
    }

    /**
     * Pad the type column of a run of `@param` tags so they line up.
     *
     * @param list<array{string, string}> $params docblock type, variable name with its `$`
     *
     * @return list<string>
     */
    public static function params(array $params): array
    {
        if ($params === []) {
            return [];
        }
        $width = \max(\array_map(static fn(array $param): int => \strlen($param[0]), $params));

        return \array_map(static fn(array $param): string => '@param ' . \str_pad($param[0], $width) . ' ' . $param[1], $params);
    }

    /**
     * The `$document = match …` block turning any create form into the create DTO.
     */
    public static function createInput(string $type): string
    {
        $create = Naming::perType($type, 'Create');
        $builder = Naming::perType($type, 'CreateBuilder');

        return "        \$document = match (true) {\n"
            . '            $input instanceof ' . $create . " => \$input,\n"
            . '            $input instanceof ' . $builder . " => \$input->build(),\n"
            . '            default => ' . $create . "::from(\$input),\n"
            . "        };\n";
    }

    public static function updateInput(string $type): string
    {
        $update = Naming::perType($type, 'Update');

        return "        \$document = match (true) {\n"
            . '            $input instanceof ' . $update . " => \$input,\n"
            . '            default => ' . $update . "::from(\$input),\n"
            . "        };\n";
    }

    /** `AlbumCreate|AlbumCreateBuilder|AlbumCreateShape`, for a `@param`. */
    public static function createDocType(string $type): string
    {
        return Naming::perType($type, 'Create') . '|' . Naming::perType($type, 'CreateBuilder') . '|' . Naming::alias($type, 'CreateShape');
    }

    /** `AlbumCreate|AlbumCreateBuilder|array`, the native type of the same parameter. */
    public static function createNativeType(string $type): string
    {
        return Naming::perType($type, 'Create') . '|' . Naming::perType($type, 'CreateBuilder') . '|array';
    }

    /** `@phpstan-import-type AlbumCreateShape from Album`. */
    public static function importAlias(string $type, string $suffix): string
    {
        return '@phpstan-import-type ' . Naming::alias($type, $suffix) . ' from ' . Naming::resource($type);
    }

    /**
     * The tail of a read whose primary data must be one resource: fail legibly on none, then
     * hydrate, through the one erasure point when the result is a projection.
     */
    public static function hydratePrimary(string $concrete, string $base, string $what, bool $projected): string
    {
        $out = '        $raw = $context->primary() ?? throw MalformedDocument::missingMember(' . Php::string('The ' . $what . ' response') . ", 'data');\n\n";
        if ($projected) {
            return $out
                . '        /** @var ' . $base . "&TProj */\n"
                . '        return $context->hydrate($raw, ' . $concrete . "::_hydrate(...));\n";
        }

        return $out . '        return ' . $concrete . "::_hydrate(\$raw, \$context);\n";
    }

    /**
     * A `Request` built for one method, through its named constructor where the runtime has one.
     * With `$indent`, the arguments go one per line beneath a call opened at that indent.
     */
    public static function request(string $method, string $uri, ?string $body = null, ?int $indent = null): string
    {
        $named = match ($method) {
            'GET' => $body === null ? 'get' : null,
            'POST' => 'post',
            'PATCH' => 'patch',
            'DELETE' => 'delete',
            default => null,
        };
        $args = $body === null ? [$uri] : [$uri, $body];
        $open = $named === null ? 'new Request(' . Php::string($method) . ', ' : 'Request::' . $named . '(';
        if ($indent === null) {
            return $open . \implode(', ', $args) . ')';
        }
        if ($named === null) {
            \array_unshift($args, Php::string($method));
            $open = 'new Request(';
        }
        $pad = \str_repeat(' ', $indent + 4);

        return $open . "\n" . \implode('', \array_map(static fn(string $arg): string => $pad . $arg . ",\n", $args)) . \str_repeat(' ', $indent) . ')';
    }
}
