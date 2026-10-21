<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit\Reads;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Emitter;
use haddowg\JsonApiCodegen\Emit\FileKey;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\Php;
use haddowg\JsonApiCodegen\Emit\UnitKind;

/**
 * An empty resource class for each type the read emitter contributes to, so its aliases and
 * constants have a declaration to land on before the read-model emitter that owns it exists.
 *
 * @internal remove when the read-model emitter is registered; it generates the real class
 */
final class ResourceClassStandIn implements Emitter
{
    public function emit(ApiDescriptor $api, GeneratorConfig $config): iterable
    {
        foreach ($api->resources as $resource) {
            if ((new ReadSources(new ReadSurface($api, $resource)))->contribution() === null) {
                continue;
            }
            $name = Naming::resource($resource->type);

            yield new ClassUnit(
                $name,
                UnitKind::FinalClass,
                FileKey::resource($resource->type),
                Php::docblock(['@generated stand-in for `components.schemas.' . Naming::accessor($resource->type) . 'Resource`'], 0, false)
                    . 'final class ' . $name . "\n{\n}\n",
                [],
                true,
            );
        }
    }
}
