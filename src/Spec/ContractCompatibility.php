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
 *
 * Contract 6 lists every member a nested object always renders in its read schema's `required`.
 * The nested shapes were already read from that `required`, so the read accessors, the array-shape
 * aliases and the tagged-union branches drop the `?` on those members with no reader change. An
 * older document lists none there, so its nested members stay optional.
 *
 * Contract 7 renders the links the server builds as link objects and describes them per context.
 * Generated code never reads a link as a string: it hands `links` to the runtime, which takes the
 * `href` of either form. An action or `202` body naming a type the document does not describe is
 * now an inline document rather than a dangling `$ref`, and the reader rejects it by pointer.
 *
 * Contract 8 splits a pivot's `meta.pivot` into a response schema, `<Type><Rel>Pivot`, and a
 * request schema, `<Type><Rel>WritePivot`; keys a relation's paths by its URI field name; and marks
 * a write-only relation `writeOnly` in the write bodies. Pivot reads are generated from the first
 * and the edge builder from the second. A relation's endpoints are found by operationId, and a
 * relation marked `writeOnly` is kept off the read model.
 *
 * Contract 9 writes a `$ref` that carries an `example`, `examples` or `default` as a single-entry
 * `allOf` holding only the `$ref`, with those keywords beside it. The reader takes that wrapper as
 * the `$ref` wherever it resolves a schema, so an enum attribute keeps its enum type and the
 * wrapper's own `default` still reaches the docblock. The per-relation links components and the
 * per-relation related collection need nothing new: links are not read, and a relation's
 * endpoints are found by operationId and their pagination from their `page` parameter.
 *
 * Contract 10 lets a custom action answer in media types other than JSON:API, each listed under
 * the success response's `content` with its own schema, and is stamped only on a document that
 * declares one; every other document stays at 9. The descriptor reads each raw media type as
 * binary, text or JSON, and the generated method takes the choice as `$accept` with a return
 * type conditional on it. A JSON body whose schema is an object becomes a value class.
 *
 * Contract 11 lets a custom action accept several request bodies, each listed under its
 * `requestBody.content`, and is stamped only on a document that declares one. The descriptor
 * already read a choice of raw request media types, so it adds only the form media types:
 * `multipart/form-data` and `application/x-www-form-urlencoded` are field arrays the runtime
 * encodes, whatever their schema, since a multipart body needs a boundary only the encoder knows.
 *
 * Contract 12 puts a whole worked `example` on each operation's JSON:API request body and success
 * responses, and is stamped only on a document that carries one. The descriptor reads them into
 * each operation's `examples`, a raw media type keeps its own `example`, and a generated method's
 * docblock quotes one as its `@example`. Nothing else changes shape, so an older document
 * generates exactly as before.
 *
 * Contract 13 gives every core error code's component an example `detail`, and since those
 * components are in every document, every document carries it. The example was already read
 * under contract 4, so each code's descriptor now has one and its class docblock quotes it.
 */
final class ContractCompatibility
{
    /** Below this the document predates the structures this codegen reads. */
    public const int MINIMUM = 1;

    /** Above this the document carries structures this codegen does not read. */
    public const int MAXIMUM = 13;

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
