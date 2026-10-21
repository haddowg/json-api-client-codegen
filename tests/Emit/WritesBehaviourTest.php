<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiClient\Exceptions\MissingRequiredMemberException;
use haddowg\JsonApiClient\Exceptions\UnknownAttributeException;
use haddowg\JsonApiClient\Support\Identifier;
use haddowg\JsonApiCodegen\Emit\Writes\WritesEmitter;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;

/**
 * Runs the generated write inputs, from the committed golden output.
 *
 * These are the behaviours PHPStan cannot see. The first is the reason the test exists: a write
 * input read with `??` instead of `array_key_exists` type-checks and turns an explicit `null`
 * into "absent", so a `PATCH` meant to clear a member sends nothing.
 *
 * The classes are reached by name because the generated namespace is not on this package's
 * autoloader. The golden output targets PHP 8.4, hence the requirement.
 */
#[CoversClass(WritesEmitter::class)]
#[RequiresPhp('>= 8.4.0')]
final class WritesBehaviourTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once \dirname(__DIR__) . '/golden/generated/autoload.php';
    }

    public function testAnExplicitNullInTheCreateArrayIsSentAsNull(): void
    {
        $document = self::document(self::call('AlbumCreate', 'from', ['title' => 'Geogaddi', 'availableFrom' => null]));

        self::assertSame(['data' => ['type' => 'albums', 'attributes' => ['title' => 'Geogaddi', 'availableFrom' => null]]], $document);
    }

    public function testAnExplicitNullInTheUpdateArrayIsSentAsNull(): void
    {
        $document = self::document(self::call('AlbumUpdate', 'from', ['availableFrom' => null]), '1');

        self::assertSame(['data' => ['type' => 'albums', 'id' => '1', 'attributes' => ['availableFrom' => null]]], $document);
    }

    public function testAnEmptyUpdateSendsNoMembers(): void
    {
        $document = self::document(self::call('AlbumUpdate', 'from', []), '1');

        self::assertSame(['data' => ['type' => 'albums', 'id' => '1']], $document);
    }

    public function testTheArrayDoorCoercesDatesEnumsAndLinkage(): void
    {
        $document = self::document(self::call('AlbumCreate', 'from', [
            'title' => 'Geogaddi',
            'releasedAt' => '2002-02-18T00:00:00+00:00',
            'availableFrom' => '2002-02-18',
            'status' => 'released',
            'artist' => Identifier::of('artists', '7'),
            'tracks' => [Identifier::of('tracks', '1'), Identifier::of('tracks', '2')],
        ]));

        self::assertSame([
            'data' => [
                'type' => 'albums',
                'attributes' => [
                    'title' => 'Geogaddi',
                    'releasedAt' => '2002-02-18T00:00:00+00:00',
                    'status' => 'released',
                    'availableFrom' => '2002-02-18',
                ],
                'relationships' => [
                    'artist' => ['data' => ['type' => 'artists', 'id' => '7']],
                    'tracks' => ['data' => [['type' => 'tracks', 'id' => '1'], ['type' => 'tracks', 'id' => '2']]],
                ],
            ],
        ], $document);
    }

    public function testAnUnknownKeyIsRejectedWithADidYouMean(): void
    {
        $this->expectException(UnknownAttributeException::class);
        $this->expectExceptionMessage('Did you mean "title"');

        self::call('AlbumUpdate', 'from', ['titel' => 'x']);
    }

    public function testAMemberOnlyTheCreateAcceptsIsRejectedOnUpdate(): void
    {
        $this->expectException(UnknownAttributeException::class);

        self::call('TrackUpdate', 'from', ['playlists' => []]);
    }

    public function testTheBuilderRejectsAMissingRequiredMember(): void
    {
        $this->expectException(MissingRequiredMemberException::class);
        $this->expectExceptionMessage('title is required');

        self::invoke(self::builder(), 'build');
    }

    public function testTheBuilderProducesTheDto(): void
    {
        $builder = self::invoke(self::builder(), 'title', 'Geogaddi');
        $builder = self::invoke($builder, 'explicit');
        $builder = self::invoke($builder, 'availableUntil', null);

        self::assertSame(
            ['data' => ['type' => 'albums', 'attributes' => ['title' => 'Geogaddi', 'explicit' => true, 'availableUntil' => null]]],
            self::document(self::invoke($builder, 'build')),
        );
    }

    public function testAnUpdateSetterReturnsANewInputAndLeavesTheReceiverAlone(): void
    {
        $empty = self::call('AlbumUpdate', 'from', []);
        $titled = self::invoke($empty, 'title', 'Geogaddi');

        self::assertNotSame($empty, $titled);
        self::assertSame(['data' => ['type' => 'albums', 'id' => '1']], self::document($empty, '1'));
        self::assertSame(['data' => ['type' => 'albums', 'id' => '1', 'attributes' => ['title' => 'Geogaddi']]], self::document($titled, '1'));
        self::assertSame($titled, self::invoke($titled, 'build'));
    }

    public function testARequiredClientIdIsSentAndAForbiddenOneIsNotAcceptedAtAll(): void
    {
        self::assertSame(
            ['data' => ['type' => 'genres', 'id' => 'ambient', 'attributes' => ['name' => 'Ambient']]],
            self::document(self::call('GenreCreate', 'from', ['id' => 'ambient', 'name' => 'Ambient'])),
        );

        $this->expectException(UnknownAttributeException::class);
        self::call('AlbumCreate', 'from', ['id' => '1', 'title' => 'x']);
    }

    private static function builder(): object
    {
        return self::instance('AlbumCreateBuilder');
    }

    /** A generated class by short name; analysis cannot see the golden output, so it stays dynamic. */
    private static function instance(string $class): object
    {
        $class = Generated::NAMESPACE . '\\' . $class;

        return new $class();
    }

    /**
     * @param array<string, mixed> $input
     */
    private static function call(string $class, string $method, array $input): object
    {
        $callable = [Generated::NAMESPACE . '\\' . $class, $method];
        self::assertIsCallable($callable);
        $result = $callable($input);
        self::assertIsObject($result);

        return $result;
    }

    private static function invoke(object $on, string $method, mixed ...$arguments): object
    {
        $callable = [$on, $method];
        self::assertIsCallable($callable);
        $result = $callable(...$arguments);
        self::assertIsObject($result);

        return $result;
    }

    private static function document(object $input, string ...$id): mixed
    {
        $callable = [$input, 'toDocument'];
        self::assertIsCallable($callable);

        return $callable(...$id);
    }
}
