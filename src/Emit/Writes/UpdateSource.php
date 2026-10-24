<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Writes;

use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;

/**
 * The update DTO (`AlbumUpdate`), which is also its own fluent form.
 *
 * Every update member is optional, so a separate builder would hold exactly this state. Each
 * setter returns a new input instead of mutating, and is `@phpstan-pure` along with the
 * constructor, so a discarded setter call is `method.resultUnused` rather than a silent no-op.
 */
final class UpdateSource
{
    /**
     * @param list<WriteMember> $members in document order, every one optional
     */
    public static function dto(WriteModel $model, array $members): string
    {
        $type = $model->resource->type;
        $resource = Naming::resource($type);
        $base = Naming::base($type);
        $class = Naming::perType($type, 'Update');

        $parameters = [];
        $parameterDoc = [];
        $imports = [Naming::alias($type, 'UpdateShape')];
        foreach ($members as $member) {
            $parameters[] = 'public readonly ' . $member->optionalNative() . ' $' . $member->variable . ' = Missing::Value';
            if ($member->relation || $member->needsDoc(true)) {
                $parameterDoc[] = '@param ' . $member->optionalDoc(true) . ' $' . $member->variable;
                if ($member->aliasDoc !== $member->doc) {
                    $imports[] = $member->aliasDoc;
                }
            }
        }
        $imports = \array_values(\array_unique($imports));
        \sort($imports, \SORT_STRING);

        $setters = [];
        foreach ($members as $member) {
            $arguments = \array_map(
                static fn(WriteMember $other): string => $other->variable . ': ' . ($other === $member ? '$' : '$this->') . $other->variable,
                $members,
            );
            $parameter = $member->presentNative() . ' $' . $member->variable . ($member->native === 'bool' && !$member->nullable ? ' = true' : '');
            $doc = $member->setterDoc($member->needsDoc(true) ? ['@param ' . $member->presentDoc(true) . ' $' . $member->variable, '', '@phpstan-pure'] : ['@phpstan-pure']);

            $setters[] = Php::docblock($doc, 4, false)
                . '    public function ' . $member->setter . '(' . $parameter . "): self\n    {\n"
                . '        return new ' . WriteSource::call('self', $arguments, 8) . ";\n"
                . "    }\n";
        }

        $constructorDoc = $parameterDoc === [] ? ['@phpstan-pure'] : [...$parameterDoc, '', '@phpstan-pure'];
        $arguments = \array_map(static fn(WriteMember $member): string => $member->variable . ': ' . WriteSource::fromArgument($member), $members);

        return Php::docblock([
            ...Php::wrap('The canonical update input for `' . $type . '`: every member optional, which is what a JSON:API `PATCH` is. The id is not a member; it comes from the handle the update is called on, so it cannot disagree with the URL.'),
            '',
            ...\array_map(static fn(string $alias): string => '@phpstan-import-type ' . $alias . ' from ' . $resource, $imports),
            '',
            WritesEmitter::operationLine($model, OperationKind::Update),
        ], 0, false)
            . 'final class ' . $class . "\n{\n"
            . "    use ImmutableConditionable;\n\n"
            . Php::docblock($constructorDoc, 4, false)
            . ($parameters === []
                ? "    public function __construct() {}\n"
                : "    public function __construct(\n" . \implode('', \array_map(static fn(string $parameter): string => '        ' . $parameter . ",\n", $parameters)) . "    ) {}\n")
            . "\n"
            . \implode("\n", $setters)
            . ($setters === [] ? '' : "\n")
            . Php::docblock(['The input itself, so an update assembled fluently ends the way a create one does.', '', '@phpstan-pure'], 4, false)
            . "    public function build(): self\n    {\n        return \$this;\n    }\n"
            . "\n"
            . CreateSource::from($model, WritesEmitter::guardConstant($model, $members, 'update'), false, 'update', $arguments)
            . "\n"
            . Php::docblock([
                ...Php::wrap('The `' . CreateSource::method($model, OperationKind::Update) . '` request document. A member holding `null` is sent and clears the server value; one holding `Missing` is not sent.', 4),
                '',
                '@return array<string, mixed>',
            ], 4, false)
            . "    public function toDocument(string \$id): array\n    {\n"
            . "        \$data = ['type' => " . $base . "::TYPE, 'id' => \$id];\n\n"
            . WriteSource::documentBody($members)
            . "    }\n"
            . "}\n";
    }
}
