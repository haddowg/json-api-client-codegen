<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiClient\Exceptions\MalformedDocument;
use haddowg\JsonApiClient\Resources\ResourceContext;
use haddowg\JsonApiClient\Resources\ResourceObject;
use haddowg\JsonApiCodegen\Emit\ReadModel\ReadModelEmitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;

/**
 * Runs the generated declared links, from the committed witness output, against an image response.
 *
 * The classes are reached by name because the generated namespace is not on this package's
 * autoloader. The golden output targets PHP 8.4, hence the requirement.
 */
#[CoversClass(ReadModelEmitter::class)]
#[RequiresPhp('>= 8.4.0')]
final class DeclaredLinksBehaviourTest extends TestCase
{
    private const array DOWNLOAD = [
        'href' => 'https://cdn.example.com/originals/img-1.png',
        'title' => 'Original',
        'type' => 'image/png',
        'meta' => ['method' => 'GET', 'expiresAt' => '2026-10-26T12:00:00+00:00', 'headers' => ['Range' => 'bytes=0-']],
    ];

    public static function setUpBeforeClass(): void
    {
        require_once \dirname(__DIR__) . '/golden/witness/autoload.php';
    }

    public function testALinkObjectReadsItsHrefStaticMembersAndCoercedMeta(): void
    {
        $download = self::call(self::links(['download' => self::DOWNLOAD]), 'download');

        self::assertIsObject($download);
        self::assertSame(ContractWitnessTest::NAMESPACE . '\\ImageDownloadLink', $download::class);
        self::assertSame('https://cdn.example.com/originals/img-1.png', self::call($download, 'href'));
        self::assertSame('Original', self::call($download, 'title'));
        self::assertSame('image/png', self::call($download, 'type'));
        self::assertSame('GET', self::call($download, 'meta', 'method'));
        self::assertEquals(new \DateTimeImmutable('2026-10-26T12:00:00+00:00'), self::call($download, 'meta', 'expiresAt'));
        self::assertSame(['Range' => 'bytes=0-'], self::call($download, 'meta', 'headers'));
    }

    public function testThePropertyHooksReadTheSameValues(): void
    {
        $links = self::links(['download' => self::DOWNLOAD, 'thumbnail' => 'https://cdn.example.com/thumbs/img-1.png']);

        self::assertSame('https://cdn.example.com/originals/img-1.png', self::property($links, 'download', 'href'));
        self::assertEquals(new \DateTimeImmutable('2026-10-26T12:00:00+00:00'), self::property($links, 'download', 'meta', 'expiresAt'));
        self::assertSame('https://cdn.example.com/thumbs/img-1.png', self::property($links, 'thumbnail'));
    }

    public function testABareLinkReadsAsItsUrlInEitherForm(): void
    {
        self::assertSame('https://cdn.example.com/t.png', self::call(self::links(['thumbnail' => 'https://cdn.example.com/t.png']), 'thumbnail'));
        self::assertSame('https://cdn.example.com/t.png', self::call(self::links(['thumbnail' => ['href' => 'https://cdn.example.com/t.png']]), 'thumbnail'));
    }

    public function testALinkTheResponseLeftOutReadsAsNull(): void
    {
        $links = self::links([]);

        self::assertNull(self::call($links, 'download'));
        self::assertNull(self::call($links, 'thumbnail'));
    }

    public function testAMetaMemberARequestCanHideReadsAsNull(): void
    {
        $download = self::call(self::links(['download' => [...self::DOWNLOAD, 'meta' => ['method' => 'GET', 'expiresAt' => '2026-10-26T12:00:00+00:00']]]), 'download');

        self::assertIsObject($download);
        self::assertNull(self::call($download, 'meta', 'headers'));
    }

    public function testAMissingRequiredMetaMemberIsMalformed(): void
    {
        $download = self::call(self::links(['download' => [...self::DOWNLOAD, 'meta' => ['method' => 'GET']]]), 'download');
        self::assertIsObject($download);

        $this->expectException(MalformedDocument::class);
        $this->expectExceptionMessage('The meta of the "download" link of the "images" resource has a "expiresAt" member that is not a date/time string.');

        self::call($download, 'meta', 'expiresAt');
    }

    public function testAnUndeclaredLinkStaysInTheLooseMap(): void
    {
        $image = self::image(['download' => self::DOWNLOAD, 'mirror' => 'https://mirror.example.com/img-1.png']);

        self::assertSame('https://mirror.example.com/img-1.png', $image->_link('mirror'));
        self::assertArrayHasKey('mirror', $image->_links());
    }

    /**
     * @param array<string, mixed> $links
     */
    private static function links(array $links): object
    {
        $declared = self::call(self::image($links), '_declaredLinks');
        self::assertIsObject($declared);

        return $declared;
    }

    /** A chain of generated accessors, called by name since analysis cannot see the golden output. */
    private static function call(object $object, string ...$methods): mixed
    {
        $value = $object;
        foreach ($methods as $method) {
            self::assertIsObject($value);
            $read = [$value, $method];
            self::assertIsCallable($read);
            $value = $read();
        }

        return $value;
    }

    /** A chain of generated property hooks, read by name. */
    private static function property(object $object, string ...$names): mixed
    {
        $value = $object;
        foreach ($names as $name) {
            self::assertIsObject($value);
            self::assertTrue(\property_exists($value, $name));
            $value = $value->{$name};
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $links
     */
    private static function image(array $links): ResourceObject
    {
        $raw = ['type' => 'images', 'id' => 'img-1', 'links' => ['self' => 'https://api.example.com/images/img-1', ...$links], 'attributes' => ['url' => 'https://cdn.example.com/cover.png'], 'relationships' => []];

        $hydrate = [ContractWitnessTest::NAMESPACE . '\\Image', '_hydrate'];
        self::assertIsCallable($hydrate);
        $image = $hydrate($raw, ResourceContext::of(['data' => $raw]));
        self::assertInstanceOf(ResourceObject::class, $image);

        return $image;
    }
}
