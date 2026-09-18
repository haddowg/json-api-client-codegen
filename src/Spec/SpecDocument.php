<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Spec;

/**
 * An OpenAPI 3.1 document, read through {@see Node}.
 *
 * The codegen targets documents emitted by `haddowg/json-api`, so this is a reader for that
 * structure rather than a general OpenAPI parser: it exposes the components, paths and `info`
 * block the descriptor is built from, and resolves local `$ref`s to `#/components/schemas/`.
 */
final class SpecDocument
{
    public const string SCHEMA_REF_PREFIX = '#/components/schemas/';

    /** @var array<array-key, Node>|null */
    private ?array $schemas = null;

    /** @var array<array-key, Node>|null */
    private ?array $paths = null;

    private function __construct(private readonly Node $root) {}

    public static function fromDecoded(mixed $decoded): self
    {
        return new self(Node::root($decoded));
    }

    /** @throws SpecException when the file is unreadable or is not a JSON object */
    public static function fromFile(string $path): self
    {
        $raw = @\file_get_contents($path);
        if ($raw === false) {
            throw SpecException::unreadable($path, 'the file could not be opened');
        }

        return self::fromJson($raw, $path);
    }

    /** @throws SpecException when the payload is not a JSON object */
    public static function fromJson(string $json, string $source): self
    {
        try {
            /** @var mixed $decoded */
            $decoded = \json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw SpecException::unreadable($source, $e->getMessage());
        }

        $document = self::fromDecoded($decoded);
        if (!$document->root->isObject()) {
            throw SpecException::wrongType('', 'an object', $decoded);
        }

        return $document;
    }

    public function root(): Node
    {
        return $this->root;
    }

    /** @throws SpecException when `openapi` is absent */
    public function openapiVersion(): string
    {
        return $this->root->get('openapi')->string();
    }

    /**
     * The contract integer the projector stamps into `info.x-generator.contract`.
     *
     * Null when the document carries no such field, which every document emitted so far does
     * not — the absence is "no signal", never a failure. See {@see ContractCompatibility}.
     *
     * @throws SpecException when the field is present but is not an integer
     */
    public function contract(): ?int
    {
        return $this->root->path('info', 'x-generator', 'contract')?->int();
    }

    /**
     * Every entry under `components.schemas`, keyed by component name.
     *
     * @return array<array-key, Node>
     *
     * @throws SpecException when the document declares no schema components
     */
    public function schemas(): array
    {
        return $this->schemas ??= $this->root->get('components')->get('schemas')->members();
    }

    /** @throws SpecException when no component of that name exists */
    public function schema(string $name): Node
    {
        return $this->findSchema($name)
            ?? throw SpecException::missing('components.schemas.' . $name);
    }

    public function findSchema(string $name): ?Node
    {
        return $this->schemas()[$name] ?? null;
    }

    /**
     * Every path item, keyed by path template and sorted so the descriptor does not inherit
     * the document's key order.
     *
     * @return array<array-key, Node>
     *
     * @throws SpecException when the document declares no paths
     */
    public function paths(): array
    {
        if ($this->paths === null) {
            $paths = $this->root->get('paths')->members();
            \ksort($paths);
            $this->paths = $paths;
        }

        return $this->paths;
    }

    public function pathItem(string $path): ?Node
    {
        return $this->paths()[$path] ?? null;
    }

    /**
     * The component name a schema node's `$ref` member points at, or null when it carries none.
     *
     * @throws SpecException when the ref is not a local `#/components/schemas/` reference
     */
    public function refName(Node $schema): ?string
    {
        $ref = $schema->find('$ref');
        if ($ref === null) {
            return null;
        }

        $target = $ref->string();
        if (!\str_starts_with($target, self::SCHEMA_REF_PREFIX)) {
            throw SpecException::malformed($ref->pointer(), 'to reference ' . self::SCHEMA_REF_PREFIX . '…');
        }

        return \substr($target, \strlen(self::SCHEMA_REF_PREFIX));
    }

    /**
     * The schema a node's `$ref` member points at, or null when the node carries no `$ref`.
     *
     * @throws SpecException when the ref names a component the document does not declare
     */
    public function follow(Node $schema): ?Node
    {
        $name = $this->refName($schema);

        return $name === null ? null : $this->schema($name);
    }

    /** Follow a `$ref` when there is one, else return the node unchanged. */
    public function dereference(Node $schema): Node
    {
        return $this->follow($schema) ?? $schema;
    }
}
