<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Enums;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\EnumDescriptor;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Emitter;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\Identifier;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\UnitKind;

/**
 * One string-backed PHP enum per enumerated schema component, all in `enums.php`.
 *
 * Case names come from `x-enum-varnames` and case docblocks from `x-enum-descriptions`. A case
 * the document gives no usable name is named from its value, with a number appended where that
 * name is already taken. The component's own description is used
 * as the summary unless it is only the case table the projector writes, which the per-case
 * docblocks already carry.
 */
final class EnumEmitter implements Emitter
{
    public function emit(ApiDescriptor $api, GeneratorConfig $config): iterable
    {
        foreach ($api->enums as $enum) {
            yield new ClassUnit($enum->name, UnitKind::Enum, FileKey::ENUMS, self::source($enum));
        }
    }

    private static function source(EnumDescriptor $enum): string
    {
        $doc = [];
        if ($enum->description !== null && !\str_starts_with(\ltrim($enum->description), '|')) {
            $doc = [...Php::wrap($enum->description), ''];
        }
        $doc[] = '@generated from `components.schemas.' . $enum->name . '`';

        $cases = [];
        $taken = [];
        foreach ($enum->cases as $case) {
            $name = $case->name ?? self::caseName($case->value);
            $unique = $name;
            for ($i = 2; isset($taken[$unique]); $i++) {
                $unique = $name . $i;
            }
            $taken[$unique] = true;
            $line = '    case ' . $unique . ' = ' . (\is_int($case->value) ? (string) $case->value : Php::string($case->value)) . ";\n";
            $cases[] = ($case->description === null ? '' : Php::docblock(Php::wrap($case->description, 4), 4)) . $line;
        }

        return Php::docblock($doc, 0, false)
            . 'enum ' . $enum->name . ($enum->intBacked() ? ": int\n{\n" : ": string\n{\n")
            . \implode("\n", $cases)
            . "}\n";
    }

    private static function caseName(int|string $value): string
    {
        $value = (string) $value;
        $name = \str_replace(' ', '', \ucwords((string) \preg_replace('/[^A-Za-z0-9]+/', ' ', $value)));

        return Identifier::isConstantName($name) ? $name : 'Case' . $name;
    }
}
