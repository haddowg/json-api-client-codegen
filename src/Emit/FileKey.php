<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

/**
 * The files generated code is grouped into, one per resource type plus shared ones.
 *
 * A resource file's name carries a hyphen because no class name can: `albums.php` is the same
 * file as the `Albums` accessor's on a case-insensitive filesystem, and a PSR-4 lookup for
 * `Albums` would load it.
 */
final class FileKey
{
    public const string ENUMS = 'enums.php';

    public const string CLIENT = 'client.php';

    public const string ERRORS = 'errors.php';

    public const string AUTOLOAD = 'autoload.php';

    public static function resource(string $type): string
    {
        if (\preg_match('/^[a-z0-9][a-z0-9-]*$/', $type) !== 1) {
            throw new \InvalidArgumentException(\sprintf('"%s" cannot name a generated file.', $type));
        }

        return 'resource-' . $type . '.php';
    }
}
