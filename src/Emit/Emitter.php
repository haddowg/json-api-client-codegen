<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;

/**
 * One artefact family: turns the descriptor into the declarations it is responsible for.
 *
 * An emitter emits exactly what the contract permits. An absent capability is an absent method or
 * class, never a runtime guard.
 */
interface Emitter
{
    /**
     * @return iterable<ClassUnit>
     */
    public function emit(ApiDescriptor $api, GeneratorConfig $config): iterable;
}
