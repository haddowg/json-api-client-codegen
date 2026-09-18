<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Spec;

use haddowg\JsonApiCodegen\Spec\Node;
use haddowg\JsonApiCodegen\Spec\SpecException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Node::class)]
#[CoversClass(SpecException::class)]
final class NodeTest extends TestCase
{
    public function testAMissingMemberNamesItsPointer(): void
    {
        $node = Node::root(['components' => ['schemas' => []]]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected components.schemas.AlbumsResource');

        $node->get('components')->get('schemas')->get('AlbumsResource');
    }

    public function testAMemberOfTheWrongTypeNamesItsPointerAndBothTypes(): void
    {
        $node = Node::root(['components' => ['schemas' => ['AlbumsResource' => ['type' => ['object']]]]]);

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected components.schemas.AlbumsResource.type to be a string, got an array');

        $node->get('components')->get('schemas')->get('AlbumsResource')->get('type')->string();
    }

    public function testTheRootPointerIsNamedInWords(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected the document root to be an object, got a string');

        Node::root('nope')->members();
    }

    public function testPathStopsAtTheFirstAbsentMember(): void
    {
        $node = Node::root(['properties' => ['type' => ['const' => 'albums']]]);

        self::assertSame('albums', $node->path('properties', 'type', 'const')?->string());
        self::assertNull($node->path('properties', 'id', 'const'));
        self::assertNull($node->path('relationships'));
    }

    public function testFindDistinguishesAnAbsentMemberFromAFalseOne(): void
    {
        $node = Node::root(['id' => false, 'meta' => null]);

        self::assertNull($node->find('attributes'));
        self::assertTrue($node->find('id')?->isFalse());
        self::assertFalse($node->find('meta')?->isFalse());
    }

    public function testItemsRejectsAnObject(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected parameters to be an array, got an object');

        Node::root(['parameters' => ['name' => 'sort']])->get('parameters')->items();
    }

    public function testStringsRejectsANonStringItemAndNamesItsIndex(): void
    {
        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected enum.1 to be a string, got a number');

        Node::root(['enum' => ['upcoming', 2]])->get('enum')->strings();
    }

    public function testMembersKeepDocumentOrderAndCarryTheirPointers(): void
    {
        $members = Node::root(['responses' => ['200' => [], '404' => []]])->get('responses')->members();

        self::assertSame([200, 404], \array_keys($members));
        self::assertSame('responses.200', $members[200]->pointer());
    }

    public function testIntAndBoolAreCheckedToo(): void
    {
        $node = Node::root(['contract' => 3, 'readOnly' => true]);

        self::assertSame(3, $node->get('contract')->int());
        self::assertTrue($node->get('readOnly')->bool());

        $this->expectException(SpecException::class);
        $this->expectExceptionMessage('expected readOnly to be an integer, got a boolean');
        $node->get('readOnly')->int();
    }
}
