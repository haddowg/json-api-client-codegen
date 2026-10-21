<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Support;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Contribution;
use haddowg\JsonApiCodegen\Emit\Emitter;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\UnitKind;
use haddowg\JsonApiCodegen\Emit\Writes\Shape;
use haddowg\JsonApiCodegen\Emit\Writes\WriteModel;

/**
 * The least of the read model the write emitter's output needs to assemble and type-check: a
 * concrete resource class to receive its contribution, a base carrying `TYPE`, and the attribute
 * shape aliases the write shapes refer to.
 *
 * Branch-local. The read-model emitter generates the real declarations, and once it is in
 * `Generator::default()` this goes, along with its use in {@see Generated}.
 */
final class ReadModelStandIn implements Emitter
{
    public function emit(ApiDescriptor $api, GeneratorConfig $config): iterable
    {
        foreach ($api->resources as $resource) {
            $type = $resource->type;
            $file = FileKey::resource($type);

            yield new ClassUnit(
                Naming::base($type),
                UnitKind::AbstractClass,
                $file,
                "/**\n * @generated stand-in for the read model\n */\nabstract class " . Naming::base($type) . " extends ResourceObject\n{\n    public const string TYPE = '" . $type . "';\n}\n",
                ['haddowg\JsonApiClient\Resources\ResourceObject'],
            );
            yield new ClassUnit(
                Naming::resource($type),
                UnitKind::FinalClass,
                $file,
                "/**\n * @generated stand-in for the read model\n */\nfinal class " . Naming::resource($type) . ' extends ' . Naming::base($type)
                    . (WriteModel::of($api, $resource)->writes() ? "\n{\n}\n" : " {}\n"),
                [],
                true,
            );

            $tags = [];
            foreach ($resource->attributes as $name => $attribute) {
                if ($attribute->format === 'object' && $attribute->schema !== null && !$attribute->schema->composed() && $attribute->schema->properties !== []) {
                    $tags[] = '@phpstan-type ' . Naming::alias($type, Naming::pascal($name) . 'Shape') . ' ' . Shape::of($attribute->schema)->doc;
                }
            }
            if ($tags !== []) {
                yield new Contribution(Naming::resource($type), 10, $tags);
            }
        }
    }
}
