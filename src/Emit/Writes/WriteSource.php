<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Writes;

use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;

/**
 * Source fragments the three write classes share: reading a member off the array door, and
 * writing one into the request document.
 */
final class WriteSource
{
    /** Short names the write classes may refer to, and the class each one imports. */
    public const array IMPORTS = [
        'Coerce' => 'haddowg\JsonApiClient\Support\Coerce',
        'Conditionable' => 'haddowg\JsonApiClient\Support\Conditionable',
        'Created' => 'haddowg\JsonApiClient\Atomic\Created',
        'CreateInput' => 'haddowg\JsonApiClient\Writes\CreateInput',
        'Identifier' => 'haddowg\JsonApiClient\Support\Identifier',
        'ImmutableConditionable' => 'haddowg\JsonApiClient\Support\ImmutableConditionable',
        'Missing' => 'haddowg\JsonApiClient\Support\Missing',
        'MissingRequiredMemberException' => 'haddowg\JsonApiClient\Exceptions\MissingRequiredMemberException',
        'ResourceContext' => 'haddowg\JsonApiClient\Resources\ResourceContext',
        'UnknownAttributeException' => 'haddowg\JsonApiClient\Exceptions\UnknownAttributeException',
        'UpdateInput' => 'haddowg\JsonApiClient\Writes\UpdateInput',
    ];

    /**
     * What must follow a short name for it to count as a use, where the name is also an English
     * word a document's prose could put in a docblock. `Created` only ever appears in a type, as
     * `Created<…>` or in a union.
     */
    private const array FOLLOWED_BY = ['Created' => '(?=[<|])'];

    /**
     * The imports a declaration's source actually uses.
     *
     * @return list<string>
     */
    public static function imports(string $source): array
    {
        $imports = [];
        foreach (self::IMPORTS as $short => $class) {
            if (\preg_match('/(?<![\\\\\w])' . $short . (self::FOLLOWED_BY[$short] ?? '(?!\w)') . '/', $source) === 1) {
                $imports[] = $class;
            }
        }

        return $imports;
    }

    /**
     * The constructor argument `from()` passes for one member.
     *
     * Present members are read with `array_key_exists`, never `??`: a member holding an explicit
     * `null` clears the server value, and `??` would fold it into the sentinel that means absent.
     */
    public static function fromArgument(WriteMember $member): string
    {
        $key = Php::string($member->name);
        $value = '$input[' . $key . ']';

        if ($member->required) {
            return match ($member->coercion) {
                Coercion::Date => '\is_string(' . $value . ') ? new \DateTimeImmutable(' . $value . ') : ' . $value,
                Coercion::Enum => ($member->intEnum ? '\is_int(' : '\is_string(') . $value . ') ? ' . $member->enum . '::from(' . $value . ') : ' . $value,
                Coercion::None => $value,
            };
        }

        $read = '\array_key_exists(' . $key . ', $input) ? ' . $value . ' : Missing::Value';

        return match ($member->coercion) {
            Coercion::Date => ($member->nullable ? 'Coerce::nullableDateTime(' : 'Coerce::dateTime(') . $read . ')',
            Coercion::Enum => $member->nullable
                ? '\array_key_exists(' . $key . ', $input) ? (' . $value . ' === null ? null : Coerce::enum(' . $member->enum . '::class, ' . $value . ')) : Missing::Value'
                : 'Coerce::enum(' . $member->enum . '::class, ' . $read . ')',
            Coercion::None => $read,
        };
    }

    /** The wire value of a member that is known to be present. */
    public static function wire(WriteMember $member, string $expression): string
    {
        $arrow = $member->nullable ? '?->' : '->';

        return match (true) {
            $member->relation => "['data' => Coerce::" . ($member->toMany ? 'linkageList(' : 'linkage(') . $expression . ')]',
            $member->dateFormat !== null => $expression . $arrow . 'format(' . $member->dateFormat . ')',
            $member->enum !== null => $expression . $arrow . 'value',
            default => $expression,
        };
    }

    /**
     * The body of a `toDocument()` from `$data` onwards: attributes then relationships, each
     * member written only when it holds a value.
     *
     * @param list<WriteMember> $members
     */
    public static function documentBody(array $members): string
    {
        $attributes = \array_values(\array_filter($members, static fn(WriteMember $member): bool => !$member->relation));
        $relations = \array_values(\array_filter($members, static fn(WriteMember $member): bool => $member->relation));
        $required = \array_values(\array_filter($attributes, static fn(WriteMember $member): bool => $member->required));

        $out = '';
        if ($attributes !== []) {
            if ($required === []) {
                $out .= "        \$attributes = [];\n";
            } else {
                $out .= '        $attributes = [' . \implode(', ', \array_map(
                    static fn(WriteMember $member): string => Php::string($member->name) . ' => ' . self::wire($member, '$this->' . $member->variable),
                    $required,
                )) . "];\n";
            }
            foreach ($attributes as $member) {
                if (!$member->required) {
                    $out .= self::whenSet($member, 'attributes');
                }
            }
            $out .= $required === []
                ? "\n        if (\$attributes !== []) {\n            \$data['attributes'] = \$attributes;\n        }\n"
                : "\n        \$data['attributes'] = \$attributes;\n";
        }

        if ($relations !== []) {
            $out .= ($out === '' ? '' : "\n") . "        \$relationships = [];\n";
            foreach ($relations as $member) {
                $out .= self::whenSet($member, 'relationships');
            }
            $out .= "\n        if (\$relationships !== []) {\n            \$data['relationships'] = \$relationships;\n        }\n";
        }

        return $out . ($out === '' ? '' : "\n") . "        return ['data' => \$data];\n";
    }

    /**
     * `_hydrate()`: how the input reads the resource a write of it answered with. A runtime
     * recorder that takes every type's input, such as an atomic batch, calls it, since only the
     * input knows its type.
     */
    public static function hydrate(WriteModel $model): string
    {
        $type = $model->resource->type;

        return Php::docblock([
            'Read the resource a write of this input answered with.',
            '',
            '@param array<string, mixed> $raw',
        ], 4, false)
            . '    public function _hydrate(array $raw, ResourceContext $context): ' . Naming::base($type) . "\n    {\n"
            . '        return ' . Naming::resource($type) . "::_hydrate(\$raw, \$context);\n"
            . "    }\n";
    }

    /**
     * Named constructor arguments, one per line, from a value expression per member.
     *
     * @param list<string> $arguments `name: value` pairs
     */
    public static function call(string $callee, array $arguments, int $indent): string
    {
        if ($arguments === []) {
            return $callee . '()';
        }
        $pad = \str_repeat(' ', $indent + 4);

        return $callee . "(\n" . \implode('', \array_map(static fn(string $argument): string => $pad . $argument . ",\n", $arguments)) . \str_repeat(' ', $indent) . ')';
    }

    private static function whenSet(WriteMember $member, string $into): string
    {
        $property = '$this->' . $member->variable;

        return "\n        if (!" . $property . " instanceof Missing) {\n"
            . '            $' . $into . '[' . Php::string($member->name) . '] = ' . self::wire($member, $property) . ";\n"
            . "        }\n";
    }
}
