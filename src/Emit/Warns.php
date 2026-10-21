<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

/**
 * An emitter that can make a choice the user should hear about, such as routing a member whose
 * name collides with a generated method through a fallback accessor.
 *
 * Read after the emitter's output has been consumed: warnings accumulate as declarations are
 * produced, and each run starts empty.
 */
interface Warns
{
    /**
     * @return list<string>
     */
    public function warnings(): array;
}
