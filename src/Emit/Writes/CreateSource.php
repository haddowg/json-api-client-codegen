<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Writes;

use haddowg\JsonApiCodegen\Descriptor\ClientIdPolicy;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;

/**
 * The create DTO (`AlbumCreate`) and the fluent builder that produces it (`AlbumCreateBuilder`).
 */
final class CreateSource
{
    /**
     * @param list<WriteMember> $members required first, then optional, each in document order
     */
    public static function dto(WriteModel $model, array $members): string
    {
        $type = $model->resource->type;
        $resource = Naming::resource($type);
        $base = Naming::base($type);
        $class = Naming::perType($type, 'Create');
        $clientId = $model->clientId();
        $required = \array_values(\array_filter($members, static fn(WriteMember $member): bool => $member->required));
        $optional = \array_values(\array_filter($members, static fn(WriteMember $member): bool => !$member->required));

        $parameters = [];
        $parameterDoc = [];
        if ($clientId === ClientIdPolicy::Required) {
            $parameters[] = 'public readonly string $id';
        }
        foreach ($required as $member) {
            $parameters[] = 'public readonly ' . $member->presentNative() . ' $' . $member->variable;
            if ($member->relation || $member->needsDoc(true)) {
                $parameterDoc[] = '@param ' . $member->presentDoc(true) . ' $' . $member->variable;
            }
        }
        if ($clientId === ClientIdPolicy::Optional) {
            $parameters[] = 'public readonly ?string $id = null';
        }
        foreach ($optional as $member) {
            $parameters[] = 'public readonly ' . $member->optionalNative() . ' $' . $member->variable . ' = Missing::Value';
            if ($member->relation || $member->needsDoc(true)) {
                $parameterDoc[] = '@param ' . $member->optionalDoc(true) . ' $' . $member->variable;
            }
        }

        $imports = [Naming::alias($type, 'CreateShape')];
        foreach ($members as $member) {
            if ($member->aliasDoc !== $member->doc && ($member->relation || $member->needsDoc(true))) {
                $imports[] = $member->aliasDoc;
            }
        }
        $imports = \array_values(\array_unique($imports));
        \sort($imports, \SORT_STRING);

        $doc = [
            ...Php::wrap('The canonical create input for `' . $type . '`, as named arguments. A missing required member is a static error and an `ArgumentCountError`; every optional member defaults to `Missing`, which keeps an absent member distinct from an explicit `null`.'),
            '',
            ...\array_map(static fn(string $alias): string => '@phpstan-import-type ' . $alias . ' from ' . $resource, $imports),
            '',
            '@implements CreateInput<' . $base . '>',
            '',
            WritesEmitter::operationLine($model, OperationKind::Create),
        ];

        $arguments = [];
        if ($clientId === ClientIdPolicy::Required) {
            $arguments[] = "id: \$input['id']";
        }
        foreach ($required as $member) {
            $arguments[] = $member->variable . ': ' . WriteSource::fromArgument($member);
        }
        if ($clientId === ClientIdPolicy::Optional) {
            $arguments[] = "id: \\array_key_exists('id', \$input) ? \$input['id'] : null";
        }
        foreach ($optional as $member) {
            $arguments[] = $member->variable . ': ' . WriteSource::fromArgument($member);
        }

        $guard = WritesEmitter::guardConstant($model, $members, 'create');
        $data = match ($clientId) {
            ClientIdPolicy::Required => "        \$data = ['type' => " . $base . "::TYPE, 'id' => \$this->id];\n",
            ClientIdPolicy::Optional => "        \$data = ['type' => " . $base . "::TYPE];\n\n        if (\$this->id !== null) {\n            \$data['id'] = \$this->id;\n        }\n",
            ClientIdPolicy::Forbidden => "        \$data = ['type' => " . $base . "::TYPE];\n",
        };

        return Php::docblock($doc, 0, false)
            . 'final class ' . $class . " implements CreateInput\n{\n"
            . ($parameterDoc === [] ? '' : Php::docblock($parameterDoc, 4, false))
            . ($parameters === []
                ? "    public function __construct() {}\n"
                : "    public function __construct(\n" . \implode('', \array_map(static fn(string $parameter): string => '        ' . $parameter . ",\n", $parameters)) . "    ) {}\n")
            . "\n"
            . self::from($model, $guard, $clientId !== ClientIdPolicy::Forbidden, 'create', $arguments)
            . "\n"
            . Php::docblock(['The `' . self::method($model, OperationKind::Create) . '` request document.', '', '@return array<string, mixed>'], 4, false)
            . "    public function toDocument(): array\n    {\n"
            . $data
            . "\n"
            . WriteSource::documentBody($members)
            . "    }\n"
            . "\n"
            . WriteSource::hydrate($model)
            . "}\n";
    }

    /**
     * @param list<WriteMember> $members required first, then optional, each in document order
     */
    public static function builder(WriteModel $model, array $members): string
    {
        $type = $model->resource->type;
        $base = Naming::base($type);
        $clientId = $model->clientId();

        $properties = [];
        $setters = [];
        $guards = [];
        $arguments = [];

        if ($clientId !== ClientIdPolicy::Forbidden) {
            $requiredId = $clientId === ClientIdPolicy::Required;
            $properties[] = $requiredId ? '    private string|Missing $id = Missing::Value;' . "\n" : '    private ?string $id = null;' . "\n";
            $setters[] = self::setter('id', $requiredId ? 'string $id' : '?string $id', []);
            if ($requiredId) {
                $guards[] = self::guard($base, 'id');
            }
            $arguments[] = 'id: $this->id';
        }

        foreach ($members as $member) {
            $properties[] = ($member->needsDoc(false) ? Php::docblock(['@var ' . $member->optionalDoc(false)], 4, false) : '')
                . '    private ' . $member->optionalNative() . ' $' . $member->variable . " = Missing::Value;\n";
            $parameter = $member->presentNative() . ' $' . $member->variable . ($member->native === 'bool' && !$member->nullable ? ' = true' : '');
            $setters[] = self::setter(
                $member->setter,
                $parameter,
                $member->setterDoc($member->needsDoc(false) ? ['@param ' . $member->presentDoc(false) . ' $' . $member->variable] : []),
                $member->variable,
            );
            if ($member->required) {
                $guards[] = self::guard($base, $member->variable, $member->name);
            }
            $arguments[] = $member->variable . ': $this->' . $member->variable;
        }

        $buildDoc = ['Produce the DTO, which is the only form that ever reaches the serialiser.'];
        if ($guards !== []) {
            $buildDoc[] = '';
            $buildDoc[] = '@throws MissingRequiredMemberException when a member the create document requires was never set';
        }

        return Php::docblock([
            ...Php::wrap('The fluent create input for `' . $type . '`, for assembly that is conditional. `build()` rejects a missing required member, which this form cannot check statically.'),
            '',
            '@implements CreateInput<' . $base . '>',
            '',
            WritesEmitter::operationLine($model, OperationKind::Create),
        ], 0, false)
            . 'final class ' . Naming::perType($type, 'CreateBuilder') . " implements CreateInput\n{\n"
            . "    use Conditionable;\n\n"
            . \implode("\n", $properties)
            . ($properties === [] ? '' : "\n")
            . \implode("\n", $setters)
            . ($setters === [] ? '' : "\n")
            . Php::docblock($buildDoc, 4, false)
            . '    public function build(): ' . Naming::perType($type, 'Create') . "\n    {\n"
            . \implode('', $guards)
            . '        return new ' . WriteSource::call(Naming::perType($type, 'Create'), $arguments, 8) . ";\n"
            . "    }\n"
            . "\n"
            . Php::docblock([
                'The `' . self::method($model, OperationKind::Create) . '` request document, from the DTO this builds.',
                '',
                '@return array<string, mixed>',
                ...($guards === [] ? [] : ['', '@throws MissingRequiredMemberException when a member the create document requires was never set']),
            ], 4, false)
            . "    public function toDocument(): array\n    {\n        return \$this->build()->toDocument();\n    }\n"
            . "\n"
            . WriteSource::hydrate($model)
            . "}\n";
    }

    /**
     * `from()`: the array door, with every key checked against the operation's writable members.
     *
     * @param list<string> $arguments
     */
    public static function from(WriteModel $model, string $guard, bool $acceptsId, string $operation, array $arguments): string
    {
        $type = $model->resource->type;
        $resource = Naming::resource($type);
        $known = $resource . '::' . $guard;

        return Php::docblock([
            'Build the DTO from the array door, checking every key against the descriptor.',
            '',
            '@param ' . Naming::alias($type, \ucfirst($operation) . 'Shape') . ' $input',
            '',
            '@throws UnknownAttributeException when a key is not a writable member of `' . $type . '`',
        ], 4, false)
            . "    public static function from(array \$input): self\n    {\n"
            . "        foreach (\\array_keys(\$input) as \$member) {\n"
            . '            if (' . ($acceptsId ? "\$member !== 'id' && " : '') . '!\in_array($member, ' . $known . ", true)) {\n"
            . '                throw UnknownAttributeException::for(' . Naming::base($type) . '::TYPE, (string) $member, ' . $known . ");\n"
            . "            }\n"
            . "        }\n\n"
            . ($arguments === [] ? '' : "        // `array_key_exists`, not `??`: an explicit null clears the member, and `??` would drop it.\n")
            . '        return new ' . WriteSource::call('self', $arguments, 8) . ";\n"
            . "    }\n";
    }

    public static function method(WriteModel $model, OperationKind $kind): string
    {
        $operation = $model->resource->operation($kind);

        return $operation === null ? $kind->value : $operation->method . ' ' . $operation->path;
    }

    /**
     * @param list<string> $doc
     */
    private static function setter(string $method, string $parameter, array $doc, string $variable = 'id'): string
    {
        return ($doc === [] ? '' : Php::docblock($doc, 4, false))
            . '    public function ' . $method . '(' . $parameter . "): self\n    {\n"
            . '        $this->' . $variable . ' = $' . $variable . ";\n\n"
            . "        return \$this;\n"
            . "    }\n";
    }

    private static function guard(string $base, string $variable, ?string $name = null): string
    {
        return '        if ($this->' . $variable . " instanceof Missing) {\n"
            . '            throw MissingRequiredMemberException::for(' . $base . '::TYPE, [' . Php::string($name ?? $variable) . "]);\n"
            . "        }\n\n";
    }
}
