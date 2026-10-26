<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * What an action's `x-safety` declares, from contract 15: whether it changes nothing, whether it
 * destroys something, and whether repeating it changes nothing more. A flag the server leaves
 * out is null, which says nothing either way. An action that declares none has no
 * `ActionSafety` at all.
 */
final class ActionSafety
{
    public function __construct(
        public readonly ?bool $readOnly,
        public readonly ?bool $destructive,
        public readonly ?bool $idempotent,
    ) {}

    /**
     * The declared flags by their `x-safety` key, leaving out any that are null.
     *
     * @return array<string, bool>
     */
    public function declared(): array
    {
        return \array_filter(
            ['readOnly' => $this->readOnly, 'destructive' => $this->destructive, 'idempotent' => $this->idempotent],
            static fn(?bool $flag): bool => $flag !== null,
        );
    }
}
