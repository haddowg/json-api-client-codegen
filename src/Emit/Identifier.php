<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Emit;

/**
 * Whether a name can be written into generated PHP where the grammar expects an identifier.
 *
 * Every name the document chooses passes through here before it becomes a class, enum, case,
 * constant, method, property or variable name. Characters are checked against the ASCII subset
 * of PHP's identifier grammar, and each position has its own reserved words: a class cannot be
 * called `List`, an enum case cannot be called `class`, a property cannot be called `$this`.
 */
final class Identifier
{
    private const string PATTERN = '/^[A-Za-z_][A-Za-z0-9_]*$/D';

    /**
     * Names no class, interface, trait or enum can take, lowercased. PHP compares them
     * case-insensitively.
     */
    private const array RESERVED_CLASS_NAMES = [
        '__class__', '__dir__', '__file__', '__function__', '__halt_compiler', '__line__', '__method__',
        '__namespace__', '__property__', '__trait__', 'abstract', 'and', 'array', 'as', 'bool', 'break',
        'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die',
        'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif',
        'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'false', 'final', 'finally', 'float', 'fn',
        'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once',
        'instanceof', 'insteadof', 'int', 'interface', 'isset', 'iterable', 'list', 'match', 'mixed',
        'namespace', 'never', 'new', 'null', 'object', 'or', 'parent', 'print', 'private', 'protected',
        'public', 'readonly', 'require', 'require_once', 'return', 'self', 'static', 'string', 'switch',
        'throw', 'trait', 'true', 'try', 'unset', 'use', 'var', 'void', 'while', 'xor', 'yield',
    ];

    /** Names no class constant, and so no enum case, can take, lowercased. */
    private const array RESERVED_CONSTANT_NAMES = ['__halt_compiler', 'class'];

    public static function isValid(string $name): bool
    {
        return \preg_match(self::PATTERN, $name) === 1;
    }

    /** A class, interface, trait or enum name. */
    public static function isClassName(string $name): bool
    {
        return self::isValid($name) && !\in_array(\strtolower($name), self::RESERVED_CLASS_NAMES, true);
    }

    /** A class constant or enum case name. */
    public static function isConstantName(string $name): bool
    {
        return self::isValid($name) && !\in_array(\strtolower($name), self::RESERVED_CONSTANT_NAMES, true);
    }

    /**
     * A name that is both a method and a property or variable, which is how a member's name is
     * used: `$this` is the only variable PHP reserves, and `__halt_compiler` the only method.
     */
    public static function isMemberName(string $name): bool
    {
        return self::isValid($name) && $name !== 'this' && \strtolower($name) !== '__halt_compiler';
    }
}
