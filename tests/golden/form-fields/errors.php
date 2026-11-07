<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Golden\FormFields;

use haddowg\JsonApiClient\Errors\Error;
use haddowg\JsonApiClient\Exceptions\CodedErrorResponse;
use haddowg\JsonApiClient\Exceptions\JsonApiErrorResponse;
use haddowg\JsonApiClient\Exceptions\UnexpectedResponse;

/**
 * Every error code this API catalogues that the runtime ships no class for, keyed by code.
 * FormFieldsClient hands it to the runtime, which consults it before its own map.
 *
 * @generated from the OpenAPI document's error catalogue
 */
final class FormFieldsErrorCodes
{
    /**
     * @var array<string, class-string<CodedErrorResponse&JsonApiErrorResponse>>
     */
    public const array MAP = [
        'SUBSCRIPTION_EXPIRED' => SubscriptionExpiredError::class,
    ];
}

/**
 * `SUBSCRIPTION_EXPIRED`: Subscription expired.
 *
 * The account's subscription has lapsed. Renew it, then retry the request.
 *
 * Example detail: The premium subscription has expired.
 *
 * @generated from `components.schemas.SubscriptionExpiredError`
 */
final class SubscriptionExpiredError extends UnexpectedResponse implements CodedErrorResponse
{
    public const string CODE = 'SUBSCRIPTION_EXPIRED';

    public const int STATUS = 402;

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
