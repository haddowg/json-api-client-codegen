<?php

declare(strict_types=1);

namespace haddowg\JsonApiClient\Tests\Generated\MusicCatalog;

use haddowg\JsonApiClient\Errors\Error;
use haddowg\JsonApiClient\Exceptions\BadRequest;
use haddowg\JsonApiClient\Exceptions\CodedErrorResponse;
use haddowg\JsonApiClient\Exceptions\Conflict;
use haddowg\JsonApiClient\Exceptions\Forbidden;
use haddowg\JsonApiClient\Exceptions\JsonApiErrorResponse;
use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Exceptions\NotAcceptable;
use haddowg\JsonApiClient\Exceptions\NotFound;
use haddowg\JsonApiClient\Exceptions\PointsAtDocumentMembers;
use haddowg\JsonApiClient\Exceptions\PointsAtQueryParameters;
use haddowg\JsonApiClient\Exceptions\ServerError;
use haddowg\JsonApiClient\Exceptions\Unauthorized;
use haddowg\JsonApiClient\Exceptions\UnexpectedResponse;
use haddowg\JsonApiClient\Exceptions\UnsupportedMediaType;
use haddowg\JsonApiClient\Exceptions\ValidationFailed;

/**
 * `ACCESS_DENIED`: Forbidden.
 *
 * The caller is authenticated but not permitted to do what the request asks. Retrying with the same
 * credentials will fail the same way; act as a user who holds the permission, or don't make the
 * request.
 *
 * Example detail: Access is denied!
 *
 * @generated from `components.schemas.Error403`
 */
final class AccessDeniedError extends Forbidden implements CodedErrorResponse
{
    public const string CODE = 'ACCESS_DENIED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `ADDITION_PROHIBITED`: Addition is prohibited.
 *
 * This relationship does not let a client add members to it. Don't retry the request; the
 * relationship changes only in the ways the API documents for it.
 *
 * Example detail: Addition to relationship 'tags' is prohibited!
 *
 * @generated from `components.schemas.Error403Pointer`
 */
final class AdditionProhibitedError extends Forbidden implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'ADDITION_PROHIBITED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `APPLICATION_ERROR`: Application error.
 *
 * The server failed to handle the request because of a fault on its side, not because of anything
 * in the request. Retrying won't help; report it to the API's maintainers.
 *
 * Example detail: An application error has occurred!
 *
 * @generated from `components.schemas.Error500`
 */
final class ApplicationError extends ServerError implements CodedErrorResponse
{
    public const string CODE = 'APPLICATION_ERROR';

    public const int STATUS = 500;

    /**
     * @param list<Error> $errors
     */
    public function __construct(array $errors = [], ?string $message = null, ?\Throwable $previous = null)
    {
        parent::__construct(self::STATUS, $errors, $message, $previous);
    }

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `ATOMIC_OPERATIONS_INVALID`: Atomic operations request is invalid.
 *
 * An operation in the Atomic Operations request is malformed: a missing or unknown `op`, or a
 * broken `ref`, `href` or `data`. The server applied none of the batch; fix the operation at
 * `source.pointer` and send the whole batch again.
 *
 * Example detail: Unknown atomic operation code 'upsert'.
 *
 * @generated from `components.schemas.Error400Pointer`
 */
final class AtomicOperationsInvalidError extends BadRequest implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'ATOMIC_OPERATIONS_INVALID';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `ATTRIBUTE_MEMBER_UNRECOGNIZED`: Attribute member is not recognized.
 *
 * The request body sent an attribute, or a key inside an object attribute, that the resource type
 * does not declare. Remove it, or correct its name, and send the request again.
 *
 * Example detail: The resource does not declare the member 'colour'!
 *
 * @generated from `components.schemas.Error400Pointer`
 */
final class AttributeMemberUnrecognizedError extends BadRequest implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'ATTRIBUTE_MEMBER_UNRECOGNIZED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `ATTRIBUTE_VALUE_INVALID`: Attribute value is invalid.
 *
 * The server could not accept the value of the attribute at `source.pointer`, for example a
 * date-time it cannot parse. Correct the value and send the request again.
 *
 * Example detail: The value for attribute "publishedAt" is invalid: Failed to parse time string
 * (next blursday) at position 0 (n): The timezone could not be found in the database
 *
 * @generated from `components.schemas.Error422Pointer`
 */
final class AttributeValueInvalidError extends ValidationFailed implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'ATTRIBUTE_VALUE_INVALID';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `AUTHENTICATION_REQUIRED`: Unauthorized.
 *
 * The request carried no credentials, or credentials the server rejected, such as an expired token.
 * Authenticate, then send the request again with valid credentials.
 *
 * Example detail: Authentication is required!
 *
 * @generated from `components.schemas.Error401`
 */
final class AuthenticationRequiredError extends Unauthorized implements CodedErrorResponse
{
    public const string CODE = 'AUTHENTICATION_REQUIRED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `CLIENT_GENERATED_ID_ALREADY_EXISTS`: Client generated ID already exists.
 *
 * A resource of this type already has the `id` the create request supplied. Generate a new id and
 * send the request again, or update the existing resource instead.
 *
 * Example detail: Client generated ID '1' already exists!
 *
 * @generated from `components.schemas.Error409Pointer`
 */
final class ClientGeneratedIdAlreadyExistsError extends Conflict implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'CLIENT_GENERATED_ID_ALREADY_EXISTS';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `CLIENT_GENERATED_ID_NOT_SUPPORTED`: Client generated ID is not supported.
 *
 * The create request supplied an `id`, but this resource type assigns its own ids. Leave `data.id`
 * out and the server generates one.
 *
 * Example detail: Client generated ID '1' is not supported!
 *
 * @generated from `components.schemas.Error403Pointer`
 */
final class ClientGeneratedIdNotSupportedError extends Forbidden implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'CLIENT_GENERATED_ID_NOT_SUPPORTED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `CLIENT_GENERATED_ID_REQUIRED`: Required client generated ID.
 *
 * This resource type expects the client to choose the id, and the create request supplied none. Add
 * `data.id` in the format the API documents and send the request again.
 *
 * Example detail: A client generated ID must be used!
 *
 * @generated from `components.schemas.Error403Pointer`
 */
final class ClientGeneratedIdRequiredError extends Forbidden implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'CLIENT_GENERATED_ID_REQUIRED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `CURSOR_MALFORMED`: Cursor is malformed.
 *
 * The cursor in the named `page` parameter could not be decoded. Use cursors exactly as the server
 * returned them in a previous page's `links`; never build or edit one.
 *
 * Example detail: The cursor supplied in 'page[after]' could not be decoded.
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class CursorMalformedError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'CURSOR_MALFORMED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `CURSOR_STALE`: Cursor is stale.
 *
 * The cursor was issued for a different sort order than this request asks for, so it no longer
 * marks a position in these results. Keep `sort` unchanged while paging, or start again from the
 * first page.
 *
 * Example detail: The cursor supplied in 'page[after]' was built for a different sort order and can
 * no longer be used.
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class CursorStaleError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'CURSOR_STALE';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `DATA_MEMBER_MISSING`: Missing `data` member at the document's top level.
 *
 * The request document has no top-level `data` member, which this request needs. Send the resource
 * object or relationship linkage under `data`.
 *
 * Example detail: Missing `data` member at the document's top level!
 *
 * @generated from `components.schemas.Error400`
 */
final class DataMemberMissingError extends BadRequest implements CodedErrorResponse
{
    public const string CODE = 'DATA_MEMBER_MISSING';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `FIELDSET_MEMBER_UNRECOGNIZED`: Fieldset member is unrecognized.
 *
 * A `fields[TYPE]` parameter names a member that type does not have. Request only the attributes
 * and relationships the type declares.
 *
 * Example detail: Fields 'colour' requested for type 'articles' can't be recognized by the
 * endpoint!
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class FieldsetMemberUnrecognizedError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'FIELDSET_MEMBER_UNRECOGNIZED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `FILTER_VALUE_INVALID`: Filter value is invalid.
 *
 * A `filter[...]` value fails the constraints that filter declares, for example text where a number
 * is expected. Correct the value of the parameter named in `source.parameter`; each failed
 * constraint is its own error.
 *
 * Example detail: The value supplied for 'filter[rating]' is invalid.
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class FilterValueInvalidError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'FILTER_VALUE_INVALID';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `FILTERING_UNRECOGNIZED`: Filtering parameter is unrecognized.
 *
 * The request filtered on a key this endpoint does not declare. Filter only on the keys the API
 * documents for this resource type.
 *
 * Example detail: Filtering parameter 'colour' can't be recognized by the endpoint!
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class FilteringUnrecognizedError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'FILTERING_UNRECOGNIZED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `FULL_REPLACEMENT_PROHIBITED`: Full replacement is prohibited.
 *
 * This relationship cannot be replaced as a whole, for example by a `PATCH` to its relationship
 * endpoint. Add or remove individual members instead, if the relationship allows that.
 *
 * Example detail: Full replacement of relationship 'tags' is prohibited!
 *
 * @generated from `components.schemas.Error403Pointer`
 */
final class FullReplacementProhibitedError extends Forbidden implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'FULL_REPLACEMENT_PROHIBITED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `INCLUSION_DEPTH_EXCEEDED`: Inclusion depth exceeded.
 *
 * An `include` path nests more relationships than the server allows. Shorten the path and fetch the
 * deeper resources with a separate request.
 *
 * Example detail: Included paths 'author.publisher.country' exceed the maximum include depth of 2
 * permitted by the endpoint!
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class InclusionDepthExceededError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'INCLUSION_DEPTH_EXCEEDED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `INCLUSION_NOT_ALLOWED`: Inclusion is not allowed.
 *
 * An `include` path names a relationship that exists but may not be included from this request.
 * Remove the path and fetch the related resources with a separate request.
 *
 * Example detail: Included paths 'author.company' are not allowed by the endpoint!
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class InclusionNotAllowedError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'INCLUSION_NOT_ALLOWED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `INCLUSION_UNRECOGNIZED`: Inclusion is unrecognized.
 *
 * An `include` path names a relationship the resource type does not have. Check each path against
 * the relationships the type documents.
 *
 * Example detail: Included paths 'auther' can't be recognized by the endpoint!
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class InclusionUnrecognizedError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'INCLUSION_UNRECOGNIZED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `INCLUSION_UNSUPPORTED`: Inclusion is unsupported.
 *
 * This endpoint does not support the `include` parameter. Remove it and fetch related resources
 * with separate requests.
 *
 * Example detail: Inclusion is not supported by the endpoint!
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class InclusionUnsupportedError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'INCLUSION_UNSUPPORTED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `INSUFFICIENT_SCOPE`: Insufficient scope.
 *
 * The access token is valid but does not grant every scope the request needs. `meta.missingScopes`
 * names the ones it lacks; request a token that grants them, or drop the `include` path named in
 * `meta.include`.
 *
 * Example detail: The access token does not grant every scope this request needs.
 *
 * @generated from `components.schemas.Error403InsufficientScope`
 */
final class InsufficientScopeError extends Forbidden implements CodedErrorResponse
{
    public const string CODE = 'INSUFFICIENT_SCOPE';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }

    /**
     * The `meta` of the first error coded `INSUFFICIENT_SCOPE`, narrowed to the members the code
     * declares.
     *
     * @throws MalformedDocument when a member the code requires is missing or of the wrong type
     */
    public function meta(): InsufficientScopeMeta
    {
        return InsufficientScopeMeta::_from($this->withCode(self::CODE)->meta ?? []);
    }
}

/**
 * The `meta` an error coded `INSUFFICIENT_SCOPE` carries, narrowed member by member: an optional
 * member of the wrong type is dropped rather than trusted, and a required one throws.
 *
 * @generated from `components.schemas.Error403InsufficientScope`, its `meta`
 */
final class InsufficientScopeMeta
{
    /**
     * @param non-empty-list<string> $requiredScopes
     * @param non-empty-list<string> $missingScopes
     */
    public function __construct(
        public readonly array $requiredScopes,
        public readonly array $missingScopes,
        public readonly ?string $include = null,
    ) {}

    /**
     * @param array<string, mixed> $raw
     */
    public static function _from(array $raw): self
    {
        $requiredScopes = $raw['requiredScopes'] ?? null;
        $missingScopes = $raw['missingScopes'] ?? null;
        $include = $raw['include'] ?? null;

        $requiredScopesValue = null;
        if (!(\is_array($requiredScopes) && \array_is_list($requiredScopes))) {
            throw MalformedDocument::unexpectedMember('The "meta" of an error coded "INSUFFICIENT_SCOPE"', 'requiredScopes', 'a list');
        }

        $requiredScopesList = [];

        foreach ($requiredScopes as $requiredScopesItem) {
            if (\is_string($requiredScopesItem)) {
                $requiredScopesList[] = $requiredScopesItem;
            }
        }

        /** @var non-empty-list<string> $requiredScopesList */
        $requiredScopesValue = $requiredScopesList;

        $missingScopesValue = null;
        if (!(\is_array($missingScopes) && \array_is_list($missingScopes))) {
            throw MalformedDocument::unexpectedMember('The "meta" of an error coded "INSUFFICIENT_SCOPE"', 'missingScopes', 'a list');
        }

        $missingScopesList = [];

        foreach ($missingScopes as $missingScopesItem) {
            if (\is_string($missingScopesItem)) {
                $missingScopesList[] = $missingScopesItem;
            }
        }

        /** @var non-empty-list<string> $missingScopesList */
        $missingScopesValue = $missingScopesList;

        $includeValue = null;
        if (\is_string($include)) {
            $includeValue = $include;
        }

        return new self($requiredScopesValue, $missingScopesValue, $includeValue);
    }
}

/**
 * `LOCAL_ID_CONFLICT`: Local id conflict.
 *
 * Two operations in the Atomic Operations request create resources of the same type under the same
 * `lid`. Give each resource a batch creates its own `lid`.
 *
 * Example detail: Local id 'new-article' is already registered for type 'articles'!
 *
 * @generated from `components.schemas.Error400`
 */
final class LocalIdConflictError extends BadRequest implements CodedErrorResponse
{
    public const string CODE = 'LOCAL_ID_CONFLICT';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `LOCAL_ID_NOT_FOUND`: Local id not found.
 *
 * An operation refers to a `lid` that no earlier operation in the batch created. Create the
 * resource under that `lid` in an earlier operation, or refer to an existing resource by `id`.
 *
 * Example detail: No resource is registered for local id 'new-article' of type 'articles'!
 *
 * @generated from `components.schemas.Error400`
 */
final class LocalIdNotFoundError extends BadRequest implements CodedErrorResponse
{
    public const string CODE = 'LOCAL_ID_NOT_FOUND';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `LOCAL_ID_NOT_SUPPORTED`: Local id is not supported.
 *
 * The request used a `lid`, which only means something inside an Atomic Operations request.
 * Identify the resource by `id`, or send the dependent writes together as one atomic batch if the
 * API offers the extension.
 *
 * Example detail: A local id (lid) is only supported within an Atomic Operations request; a
 * standalone request must identify a resource by id.
 *
 * @generated from `components.schemas.Error400Pointer`
 */
final class LocalIdNotSupportedError extends BadRequest implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'LOCAL_ID_NOT_SUPPORTED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `MEDIA_TYPE_UNACCEPTABLE`: The provided media type is unacceptable.
 *
 * Every `application/vnd.api+json` entry in the `Accept` header carries a parameter other than
 * `ext` or `profile`, or asks for an `ext` the server does not support. Accept
 * `application/vnd.api+json` with no other parameters.
 *
 * Example detail: The media type 'application/vnd.api+json; version=2' is unacceptable in the
 * 'Accept' header!
 *
 * @generated from `components.schemas.Error406Header`
 */
final class MediaTypeUnacceptableError extends NotAcceptable implements CodedErrorResponse
{
    public const string CODE = 'MEDIA_TYPE_UNACCEPTABLE';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `MEDIA_TYPE_UNSUPPORTED`: The provided media type is unsupported.
 *
 * The `Content-Type` is missing or names a media type the endpoint does not accept, or is
 * `application/vnd.api+json` with a media type parameter other than `ext` or `profile`, or with an
 * `ext` the server does not support. Send a JSON:API document as `Content-Type:
 * application/vnd.api+json` with only the parameters the server supports.
 *
 * Example detail: The media type 'application/vnd.api+json; charset=utf-8' is unsupported in the
 * 'Content-Type' header!
 *
 * @generated from `components.schemas.Error415Header`
 */
final class MediaTypeUnsupportedError extends UnsupportedMediaType implements CodedErrorResponse
{
    public const string CODE = 'MEDIA_TYPE_UNSUPPORTED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `METHOD_NOT_ALLOWED`: Method Not Allowed.
 *
 * The path exists, but not for the method the request used: the type or relationship may be
 * read-only, or the endpoint may take no such operation. The `Allow` response header lists the
 * methods the path accepts.
 *
 * @generated from `components.schemas.Error405`
 */
final class MethodNotAllowedError extends UnexpectedResponse implements CodedErrorResponse
{
    public const string CODE = 'METHOD_NOT_ALLOWED';

    public const int STATUS = 405;

    /**
     * @param list<Error> $errors
     */
    public function __construct(array $errors = [], ?string $message = null, ?\Throwable $previous = null)
    {
        parent::__construct(self::STATUS, $errors, $message, $previous);
    }

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * Every error code this API catalogues, keyed by code. MusicCatalogClient hands it to the runtime,
 * which builds an error response whose errors share one of these codes as that code's class.
 *
 * @generated from the OpenAPI document's error catalogue
 */
final class MusicCatalogErrorCodes
{
    /**
     * @var array<string, class-string<CodedErrorResponse&JsonApiErrorResponse>>
     */
    public const array MAP = [
        'ACCESS_DENIED' => AccessDeniedError::class,
        'ADDITION_PROHIBITED' => AdditionProhibitedError::class,
        'APPLICATION_ERROR' => ApplicationError::class,
        'ATOMIC_OPERATIONS_INVALID' => AtomicOperationsInvalidError::class,
        'ATTRIBUTE_MEMBER_UNRECOGNIZED' => AttributeMemberUnrecognizedError::class,
        'ATTRIBUTE_VALUE_INVALID' => AttributeValueInvalidError::class,
        'AUTHENTICATION_REQUIRED' => AuthenticationRequiredError::class,
        'CLIENT_GENERATED_ID_ALREADY_EXISTS' => ClientGeneratedIdAlreadyExistsError::class,
        'CLIENT_GENERATED_ID_NOT_SUPPORTED' => ClientGeneratedIdNotSupportedError::class,
        'CLIENT_GENERATED_ID_REQUIRED' => ClientGeneratedIdRequiredError::class,
        'CURSOR_MALFORMED' => CursorMalformedError::class,
        'CURSOR_STALE' => CursorStaleError::class,
        'DATA_MEMBER_MISSING' => DataMemberMissingError::class,
        'FIELDSET_MEMBER_UNRECOGNIZED' => FieldsetMemberUnrecognizedError::class,
        'FILTERING_UNRECOGNIZED' => FilteringUnrecognizedError::class,
        'FILTER_VALUE_INVALID' => FilterValueInvalidError::class,
        'FULL_REPLACEMENT_PROHIBITED' => FullReplacementProhibitedError::class,
        'INCLUSION_DEPTH_EXCEEDED' => InclusionDepthExceededError::class,
        'INCLUSION_NOT_ALLOWED' => InclusionNotAllowedError::class,
        'INCLUSION_UNRECOGNIZED' => InclusionUnrecognizedError::class,
        'INCLUSION_UNSUPPORTED' => InclusionUnsupportedError::class,
        'INSUFFICIENT_SCOPE' => InsufficientScopeError::class,
        'LOCAL_ID_CONFLICT' => LocalIdConflictError::class,
        'LOCAL_ID_NOT_FOUND' => LocalIdNotFoundError::class,
        'LOCAL_ID_NOT_SUPPORTED' => LocalIdNotSupportedError::class,
        'MEDIA_TYPE_UNACCEPTABLE' => MediaTypeUnacceptableError::class,
        'MEDIA_TYPE_UNSUPPORTED' => MediaTypeUnsupportedError::class,
        'METHOD_NOT_ALLOWED' => MethodNotAllowedError::class,
        'NO_RESOURCE_REGISTERED' => NoResourceRegisteredError::class,
        'PAGINATION_KIND_UNKNOWN' => PaginationKindUnknownError::class,
        'QUERY_PARAM_MALFORMED' => QueryParamMalformedError::class,
        'QUERY_PARAM_UNRECOGNIZED' => QueryParamUnrecognizedError::class,
        'RELATED_ATTRIBUTE_OWNER_MISSING' => RelatedAttributeOwnerMissingError::class,
        'RELATIONSHIP_COUNT_NOT_ALLOWED' => RelationshipCountNotAllowedError::class,
        'RELATIONSHIP_NOT_EXISTS' => RelationshipNotExistsError::class,
        'RELATIONSHIP_TYPE_INAPPROPRIATE' => RelationshipTypeInappropriateError::class,
        'REMOVAL_PROHIBITED' => RemovalProhibitedError::class,
        'REQUEST_BODY_INVALID_JSON' => RequestBodyInvalidJsonError::class,
        'REQUEST_BODY_INVALID_JSON_API' => RequestBodyInvalidJsonApiError::class,
        'REQUIRED_TOP_LEVEL_MEMBERS_MISSING' => RequiredTopLevelMembersMissingError::class,
        'RESOURCE_IDENTIFIER_ID_INVALID' => ResourceIdentifierIdInvalidError::class,
        'RESOURCE_IDENTIFIER_ID_MISSING' => ResourceIdentifierIdMissingError::class,
        'RESOURCE_IDENTIFIER_LID_INVALID' => ResourceIdentifierLidInvalidError::class,
        'RESOURCE_IDENTIFIER_TYPE_INVALID' => ResourceIdentifierTypeInvalidError::class,
        'RESOURCE_IDENTIFIER_TYPE_MISSING' => ResourceIdentifierTypeMissingError::class,
        'RESOURCE_ID_CONFLICT' => ResourceIdConflictError::class,
        'RESOURCE_ID_INVALID' => ResourceIdInvalidError::class,
        'RESOURCE_ID_MISSING' => ResourceIdMissingError::class,
        'RESOURCE_ID_UNDECODABLE' => ResourceIdUndecodableError::class,
        'RESOURCE_NOT_FOUND' => ResourceNotFoundError::class,
        'RESOURCE_TYPE_MISSING' => ResourceTypeMissingError::class,
        'RESOURCE_TYPE_UNACCEPTABLE' => ResourceTypeUnacceptableError::class,
        'RESPONSE_BODY_INVALID_JSON' => ResponseBodyInvalidJsonError::class,
        'RESPONSE_BODY_INVALID_JSON_API' => ResponseBodyInvalidJsonApiError::class,
        'SORTING_UNRECOGNIZED' => SortingUnrecognizedError::class,
        'SORTING_UNSUPPORTED' => SortingUnsupportedError::class,
        'TOP_LEVEL_MEMBERS_INCOMPATIBLE' => TopLevelMembersIncompatibleError::class,
        'TOP_LEVEL_MEMBER_NOT_ALLOWED' => TopLevelMemberNotAllowedError::class,
    ];
}

/**
 * `NO_RESOURCE_REGISTERED`: No resource registered.
 *
 * The server tried to use a resource type it has nothing registered for, which is a configuration
 * fault on its side. Retrying won't help; report it to the API's maintainers.
 *
 * Example detail: No resource is registered for type "articles".
 *
 * @generated from `components.schemas.Error500`
 */
final class NoResourceRegisteredError extends ServerError implements CodedErrorResponse
{
    public const string CODE = 'NO_RESOURCE_REGISTERED';

    public const int STATUS = 500;

    /**
     * @param list<Error> $errors
     */
    public function __construct(array $errors = [], ?string $message = null, ?\Throwable $previous = null)
    {
        parent::__construct(self::STATUS, $errors, $message, $previous);
    }

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `PAGINATION_KIND_UNKNOWN`: Pagination kind is not supported.
 *
 * `page[kind]` names a pagination strategy this collection does not offer. Use one of the
 * strategies the `detail` lists, or leave `page[kind]` out.
 *
 * Example detail: The pagination strategy 'page[kind]=offset' is not supported; use one of: page,
 * cursor.
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class PaginationKindUnknownError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'PAGINATION_KIND_UNKNOWN';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `QUERY_PARAM_MALFORMED`: Query parameter is malformed.
 *
 * A query parameter has the wrong shape, such as `fields` sent without a type key or `include` sent
 * as a list instead of a comma-separated string. Fix the parameter named in `source.parameter`.
 *
 * Example detail: Query parameter 'include' is malformed!
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class QueryParamMalformedError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'QUERY_PARAM_MALFORMED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `QUERY_PARAM_UNRECOGNIZED`: Query parameter is unrecognized.
 *
 * The request used a query parameter this endpoint does not recognize. Remove it, or check its
 * spelling against the parameters the API documents.
 *
 * Example detail: Query parameter 'colour' can't be recognized by the endpoint!
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class QueryParamUnrecognizedError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'QUERY_PARAM_UNRECOGNIZED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RELATED_ATTRIBUTE_OWNER_MISSING`: Related attribute owner missing.
 *
 * The request wrote an attribute the server stores on a related resource, and that related resource
 * does not exist. Set the relationship first, or leave the attribute out.
 *
 * Example detail: Cannot write attribute "city": its related "address" is not present.
 *
 * @generated from `components.schemas.Error422Pointer`
 */
final class RelatedAttributeOwnerMissingError extends ValidationFailed implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'RELATED_ATTRIBUTE_OWNER_MISSING';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RELATIONSHIP_COUNT_NOT_ALLOWED`: Relationship count is not allowed.
 *
 * `withCount` names a relationship that is not a countable to-many relationship of this resource.
 * Ask for counts only on the relationships the API marks as countable.
 *
 * Example detail: Counted relationships 'author' are not countable to-many relationships of this
 * resource!
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class RelationshipCountNotAllowedError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'RELATIONSHIP_COUNT_NOT_ALLOWED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RELATIONSHIP_NOT_EXISTS`: The requested relationship does not exist!.
 *
 * The request names a relationship this resource type does not have, or one whose endpoint the API
 * does not expose. Check the relationship name against the ones the type documents.
 *
 * Example detail: The requested relationship 'reviewers' does not exist!
 *
 * @generated from `components.schemas.Error404`
 */
final class RelationshipNotExistsError extends NotFound implements CodedErrorResponse
{
    public const string CODE = 'RELATIONSHIP_NOT_EXISTS';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RELATIONSHIP_TYPE_INAPPROPRIATE`: Relationship type is inappropriate.
 *
 * The relationship data has the wrong cardinality: a list for a to-one relationship, a single
 * identifier for a to-many one, or a `POST` or `DELETE` to a to-one relationship endpoint. Send
 * linkage that matches the relationship.
 *
 * Example detail: The provided relationship 'author' is of type of to-many, but to-one is expected!
 *
 * @generated from `components.schemas.Error400Pointer`
 */
final class RelationshipTypeInappropriateError extends BadRequest implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'RELATIONSHIP_TYPE_INAPPROPRIATE';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `REMOVAL_PROHIBITED`: Removal is prohibited.
 *
 * This relationship does not let a client remove members from it or clear it. Don't retry the
 * request; the relationship changes only in the ways the API documents for it.
 *
 * Example detail: Removal of relationship 'tags' is prohibited!
 *
 * @generated from `components.schemas.Error403Pointer`
 */
final class RemovalProhibitedError extends Forbidden implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'REMOVAL_PROHIBITED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `REQUEST_BODY_INVALID_JSON_API`: Request body is an invalid JSON:API document.
 *
 * The request body is JSON but breaks the JSON:API document structure or the schema the server
 * validates this resource against. Each violation is its own error, with `source.pointer` set where
 * the server could locate it.
 *
 * Example detail: The data must match the type: object
 *
 * @generated from `components.schemas.Error400`
 */
final class RequestBodyInvalidJsonApiError extends BadRequest implements CodedErrorResponse
{
    public const string CODE = 'REQUEST_BODY_INVALID_JSON_API';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `REQUEST_BODY_INVALID_JSON`: Request body is an invalid JSON document.
 *
 * The request body is not well-formed JSON. Fix the syntax the `detail` reports and send the
 * request again.
 *
 * Example detail: Request body is an invalid JSON document: 'Syntax error'!
 *
 * @generated from `components.schemas.Error400RequestBodyInvalidJson`
 */
final class RequestBodyInvalidJsonError extends BadRequest implements CodedErrorResponse
{
    public const string CODE = 'REQUEST_BODY_INVALID_JSON';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }

    /**
     * The `meta` of the first error coded `REQUEST_BODY_INVALID_JSON`, narrowed to the members the
     * code declares, or null when it carries none.
     */
    public function meta(): ?RequestBodyInvalidJsonMeta
    {
        $meta = $this->withCode(self::CODE)->meta ?? [];

        return $meta === [] ? null : RequestBodyInvalidJsonMeta::_from($meta);
    }
}

/**
 * The `meta` an error coded `REQUEST_BODY_INVALID_JSON` carries, narrowed member by member: an
 * optional member of the wrong type is dropped rather than trusted, and a required one throws.
 *
 * @generated from `components.schemas.Error400RequestBodyInvalidJson`, its `meta`
 */
final class RequestBodyInvalidJsonMeta
{
    public function __construct(
        public readonly ?string $original = null,
    ) {}

    /**
     * @param array<string, mixed> $raw
     */
    public static function _from(array $raw): self
    {
        $original = $raw['original'] ?? null;

        $originalValue = null;
        if (\is_string($original)) {
            $originalValue = $original;
        }

        return new self($originalValue);
    }
}

/**
 * `REQUIRED_TOP_LEVEL_MEMBERS_MISSING`: Required top-level members are missing.
 *
 * The request body is not a JSON object, or has none of the top-level members `data`, `errors` and
 * `meta`. Send a JSON object with the resource under `data`.
 *
 * Example detail: A document must contain at least one of the following top-level members: "data",
 * "errors", "meta"
 *
 * @generated from `components.schemas.Error400`
 */
final class RequiredTopLevelMembersMissingError extends BadRequest implements CodedErrorResponse
{
    public const string CODE = 'REQUIRED_TOP_LEVEL_MEMBERS_MISSING';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RESOURCE_ID_CONFLICT`: Resource id conflict.
 *
 * The `id` in the update body is not the `id` in the URL. Send the id of the resource the URL
 * addresses as `data.id`.
 *
 * Example detail: Resource id '12' does not match the endpoint id '7'!
 *
 * @generated from `components.schemas.Error409Pointer`
 */
final class ResourceIdConflictError extends Conflict implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'RESOURCE_ID_CONFLICT';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RESOURCE_ID_INVALID`: Resource ID is invalid.
 *
 * The resource object's `id` is not a string, such as a number. Send `data.id` as a JSON string.
 *
 * Example detail: The resource ID must be a string instead of integer!
 *
 * @generated from `components.schemas.Error400Pointer`
 */
final class ResourceIdInvalidError extends BadRequest implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'RESOURCE_ID_INVALID';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RESOURCE_ID_MISSING`: Resource ID is missing.
 *
 * The update request's resource object has no `id`. Include the id of the resource being updated as
 * `data.id`.
 *
 * Example detail: A resource ID must be included in the document!
 *
 * @generated from `components.schemas.Error400Pointer`
 */
final class ResourceIdMissingError extends BadRequest implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'RESOURCE_ID_MISSING';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RESOURCE_ID_UNDECODABLE`: Resource ID is undecodable.
 *
 * The client-generated `id` is not in a format the server can decode into one of its own ids. Send
 * an `id` in the format the API documents for this resource type.
 *
 * Example detail: The resource ID 'not-an-id' could not be decoded!
 *
 * @generated from `components.schemas.Error422Pointer`
 */
final class ResourceIdUndecodableError extends ValidationFailed implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'RESOURCE_ID_UNDECODABLE';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RESOURCE_IDENTIFIER_ID_INVALID`: Resource identifier ID is invalid.
 *
 * A resource identifier in the request carries an `id` that is not a string, such as a number. Send
 * every `id` as a JSON string.
 *
 * Example detail: The resource ID must be a string instead of integer!
 *
 * @generated from `components.schemas.Error400`
 */
final class ResourceIdentifierIdInvalidError extends BadRequest implements CodedErrorResponse
{
    public const string CODE = 'RESOURCE_IDENTIFIER_ID_INVALID';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RESOURCE_IDENTIFIER_ID_MISSING`: An ID for the resource identifier is missing.
 *
 * A resource identifier in the request has neither an `id` nor a `lid`. Identify each related
 * resource by its `id`, or by a `lid` inside an Atomic Operations request.
 *
 * Example detail: An ID or local ID (lid) for the resource identifier must be included!
 *
 * @generated from `components.schemas.Error400`
 */
final class ResourceIdentifierIdMissingError extends BadRequest implements CodedErrorResponse
{
    public const string CODE = 'RESOURCE_IDENTIFIER_ID_MISSING';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RESOURCE_IDENTIFIER_LID_INVALID`: Resource identifier local ID is invalid.
 *
 * A resource identifier in the request carries a `lid` that is not a string. Send every `lid` as a
 * JSON string.
 *
 * Example detail: The resource local ID (lid) must be a string instead of integer!
 *
 * @generated from `components.schemas.Error400`
 */
final class ResourceIdentifierLidInvalidError extends BadRequest implements CodedErrorResponse
{
    public const string CODE = 'RESOURCE_IDENTIFIER_LID_INVALID';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RESOURCE_IDENTIFIER_TYPE_INVALID`: Resource identifier type is invalid.
 *
 * A resource identifier in the request carries a `type` that is not a string. Send every `type` as
 * a JSON string.
 *
 * Example detail: The resource type must be a string instead of integer!
 *
 * @generated from `components.schemas.Error400`
 */
final class ResourceIdentifierTypeInvalidError extends BadRequest implements CodedErrorResponse
{
    public const string CODE = 'RESOURCE_IDENTIFIER_TYPE_INVALID';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RESOURCE_IDENTIFIER_TYPE_MISSING`: A type for the resource identifier is missing.
 *
 * A resource identifier in the request has no `type`. Add the related resource's `type` to every
 * identifier.
 *
 * Example detail: A type for the resource identifier must be included!
 *
 * @generated from `components.schemas.Error400`
 */
final class ResourceIdentifierTypeMissingError extends BadRequest implements CodedErrorResponse
{
    public const string CODE = 'RESOURCE_IDENTIFIER_TYPE_MISSING';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RESOURCE_NOT_FOUND`: Resource not found.
 *
 * The resource the request addresses does not exist. Check the type and id in the URL; a deleted
 * resource stays not found.
 *
 * Example detail: The requested resource is not found!
 *
 * @generated from `components.schemas.Error404`
 */
final class ResourceNotFoundError extends NotFound implements CodedErrorResponse
{
    public const string CODE = 'RESOURCE_NOT_FOUND';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RESOURCE_TYPE_MISSING`: Resource type is missing.
 *
 * The resource object in `data` has no `type`. Add the type of the resource the endpoint serves.
 *
 * Example detail: A resource type must be included in the document!
 *
 * @generated from `components.schemas.Error400Pointer`
 */
final class ResourceTypeMissingError extends BadRequest implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'RESOURCE_TYPE_MISSING';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RESOURCE_TYPE_UNACCEPTABLE`: Resource type is unacceptable.
 *
 * The `type` in the request is not one this endpoint or relationship accepts. Send the type the
 * endpoint serves, or one the relationship documents for its related resources.
 *
 * Example detail: Resource type 'people' is unacceptable!
 *
 * @generated from `components.schemas.Error409Pointer`
 */
final class ResourceTypeUnacceptableError extends Conflict implements CodedErrorResponse
{
    use PointsAtDocumentMembers;

    public const string CODE = 'RESOURCE_TYPE_UNACCEPTABLE';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RESPONSE_BODY_INVALID_JSON_API`: Response body is an invalid JSON:API document.
 *
 * The server produced a response that breaks the JSON:API document structure, which is a fault on
 * its side. Retrying won't help; report it to the API's maintainers.
 *
 * Example detail: The data must match the type: object
 *
 * @generated from `components.schemas.Error500`
 */
final class ResponseBodyInvalidJsonApiError extends ServerError implements CodedErrorResponse
{
    public const string CODE = 'RESPONSE_BODY_INVALID_JSON_API';

    public const int STATUS = 500;

    /**
     * @param list<Error> $errors
     */
    public function __construct(array $errors = [], ?string $message = null, ?\Throwable $previous = null)
    {
        parent::__construct(self::STATUS, $errors, $message, $previous);
    }

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `RESPONSE_BODY_INVALID_JSON`: Response body is an invalid JSON document.
 *
 * The server produced a response body that is not valid JSON, which is a fault on its side.
 * Retrying won't help; report it to the API's maintainers.
 *
 * Example detail: Response body is an invalid JSON document: 'Syntax error'!
 *
 * @generated from `components.schemas.Error500ResponseBodyInvalidJson`
 */
final class ResponseBodyInvalidJsonError extends ServerError implements CodedErrorResponse
{
    public const string CODE = 'RESPONSE_BODY_INVALID_JSON';

    public const int STATUS = 500;

    /**
     * @param list<Error> $errors
     */
    public function __construct(array $errors = [], ?string $message = null, ?\Throwable $previous = null)
    {
        parent::__construct(self::STATUS, $errors, $message, $previous);
    }

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }

    /**
     * The `meta` of the first error coded `RESPONSE_BODY_INVALID_JSON`, narrowed to the members the
     * code declares, or null when it carries none.
     */
    public function meta(): ?ResponseBodyInvalidJsonMeta
    {
        $meta = $this->withCode(self::CODE)->meta ?? [];

        return $meta === [] ? null : ResponseBodyInvalidJsonMeta::_from($meta);
    }
}

/**
 * The `meta` an error coded `RESPONSE_BODY_INVALID_JSON` carries, narrowed member by member: an
 * optional member of the wrong type is dropped rather than trusted, and a required one throws.
 *
 * @generated from `components.schemas.Error500ResponseBodyInvalidJson`, its `meta`
 */
final class ResponseBodyInvalidJsonMeta
{
    public function __construct(
        public readonly ?string $original = null,
    ) {}

    /**
     * @param array<string, mixed> $raw
     */
    public static function _from(array $raw): self
    {
        $original = $raw['original'] ?? null;

        $originalValue = null;
        if (\is_string($original)) {
            $originalValue = $original;
        }

        return new self($originalValue);
    }
}

/**
 * `SORTING_UNRECOGNIZED`: Sorting parameter is unrecognized.
 *
 * The `sort` parameter names a field this endpoint cannot sort by. Sort only on the fields the API
 * documents for this resource type.
 *
 * Example detail: Sorting parameter 'popularity' can't be recognized by the endpoint!
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class SortingUnrecognizedError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'SORTING_UNRECOGNIZED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `SORTING_UNSUPPORTED`: Sorting is unsupported.
 *
 * This endpoint does not support sorting. Remove the `sort` parameter.
 *
 * Example detail: Sorting is not supported by the endpoint!
 *
 * @generated from `components.schemas.Error400Parameter`
 */
final class SortingUnsupportedError extends BadRequest implements CodedErrorResponse
{
    use PointsAtQueryParameters;

    public const string CODE = 'SORTING_UNSUPPORTED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `TOP_LEVEL_MEMBER_NOT_ALLOWED`: Top-level member is not allowed.
 *
 * The request body has a top-level `included` member but no `data`. Remove `included`, or send it
 * alongside `data`.
 *
 * Example detail: If a document does not contain a top-level "data" key, the "included" member must
 * not be present either.
 *
 * @generated from `components.schemas.Error400`
 */
final class TopLevelMemberNotAllowedError extends BadRequest implements CodedErrorResponse
{
    public const string CODE = 'TOP_LEVEL_MEMBER_NOT_ALLOWED';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}

/**
 * `TOP_LEVEL_MEMBERS_INCOMPATIBLE`: Top-level members are incompatible.
 *
 * The request body has both `data` and `errors` at the top level, which JSON:API forbids. Send the
 * resource under `data` alone.
 *
 * Example detail: The members "data" and "errors" cannot coexist in the same document
 *
 * @generated from `components.schemas.Error400`
 */
final class TopLevelMembersIncompatibleError extends BadRequest implements CodedErrorResponse
{
    public const string CODE = 'TOP_LEVEL_MEMBERS_INCOMPATIBLE';

    /**
     * @param list<Error> $errors
     */
    public static function fromErrors(array $errors, ?string $message = null, ?\Throwable $previous = null): static
    {
        return new self($errors, $message, $previous);
    }
}
