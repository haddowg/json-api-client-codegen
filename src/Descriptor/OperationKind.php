<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Descriptor;

/**
 * The standard operations a resource type can expose. Only the ones the document advertises
 * reach the descriptor, so an absent key is an absent method on the generated client.
 */
enum OperationKind: string
{
    case FetchMany = 'fetchMany';
    case FetchOne = 'fetchOne';
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case FetchRelated = 'fetchRelated';
    case FetchRelationship = 'fetchRelationship';
}
