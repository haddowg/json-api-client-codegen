<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Spec;

/**
 * The contract range this codegen reads.
 *
 * `info.x-generator.contract` is one monotonic integer the projector bumps whenever the emitted
 * structure changes. Two failure modes, and only one of them needs this: a server OLDER than the
 * codegen expects is caught by the reader, because the structure it wants is simply absent. A
 * server NEWER than the codegen is not — the new structure goes unread and the client silently
 * comes out missing capabilities the server offers. That is the case the warning exists for.
 *
 * No document emits the field yet, so the common answer is "no signal, proceed".
 */
final class ContractCompatibility
{
    /** Below this the document predates the structures this codegen reads. */
    public const int MINIMUM = 1;

    /** Above this the document carries structures this codegen does not read. */
    public const int MAXIMUM = 1;

    /**
     * Check a document's declared contract against the supported range.
     *
     * @return string|null a warning to surface to the user, or null when there is nothing to say
     *
     * @throws UnsupportedContractException when the document predates the supported range
     */
    public static function check(?int $contract): ?string
    {
        if ($contract === null) {
            return null;
        }

        if ($contract < self::MINIMUM) {
            throw new UnsupportedContractException($contract, self::MINIMUM, self::MAXIMUM);
        }

        if ($contract > self::MAXIMUM) {
            return \sprintf(
                'the document declares contract %d and this codegen reads up to %d. It describes capabilities that were not generated; upgrade the codegen to pick them up.',
                $contract,
                self::MAXIMUM,
            );
        }

        return null;
    }
}
