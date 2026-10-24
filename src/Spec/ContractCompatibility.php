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
 * Documents from `json-api` before 1.1.0 carry no contract, which is "no signal, proceed".
 *
 * Contract 3 adds `x-paginator` on page schemas. It is read where present; a contract-2 document
 * still generates, with pagination inferred from canonical member names.
 *
 * Contract 4 gives each error code's component a `description` and, for some codes, an example
 * `detail`. Both are read into the code's descriptor and written into its class docblock.
 *
 * Contract 5 projects each validation constraint into every schema it holds for, makes a read
 * attribute nullable unless the server guarantees a value, states a page member's `default` and
 * cap, and drops a write-only relation from the read schemas. The constraints are read per
 * schema and narrow the generated types; the rest needs no reader change, because nullability
 * and members were already read from the schema they appear in.
 */
final class ContractCompatibility
{
    /** Below this the document predates the structures this codegen reads. */
    public const int MINIMUM = 1;

    /** Above this the document carries structures this codegen does not read. */
    public const int MAXIMUM = 5;

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
