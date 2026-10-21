<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\AttributeDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ClientIdPolicy;
use haddowg\JsonApiCodegen\Descriptor\OperationDescriptor;
use haddowg\JsonApiCodegen\Descriptor\OperationKind;
use haddowg\JsonApiCodegen\Descriptor\PaginatorDescriptor;
use haddowg\JsonApiCodegen\Descriptor\PaginatorKind;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\Contribution;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\ReadModel\Guard;
use haddowg\JsonApiCodegen\Emit\ReadModel\ReadModelEmitter;
use haddowg\JsonApiCodegen\Emit\ReadModel\Shape;
use haddowg\JsonApiCodegen\Tests\Support\ArtefactParity;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReadModelEmitter::class)]
#[CoversClass(Shape::class)]
#[CoversClass(Guard::class)]
final class ReadModelEmitterTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function albumDeclarations(): iterable
    {
        yield 'AlbumHasArtist' => ['AlbumHasArtist'];
        yield 'AlbumHasTracks' => ['AlbumHasTracks'];
        yield 'AlbumBase' => ['AlbumBase'];
    }

    #[DataProvider('albumDeclarations')]
    public function testAlbumDeclarationMatchesTheArtefact(string $declaration): void
    {
        ArtefactParity::assertMatches($declaration, 'resource-albums.php', Generated::file('resource-albums.php'));
    }

    /**
     * `Album` also carries the aliases and descriptor constants the write and read emitters
     * contribute. They are stripped from the artefact here, leaving exactly what this emitter
     * owns: the class, its own attribute-shape aliases, and every member.
     */
    public function testAlbumMatchesTheArtefactWithEveryEmittersContributions(): void
    {
        ArtefactParity::assertMatches('Album', 'resource-albums.php', Generated::file('resource-albums.php'));
    }

    public function testAn83TargetEmitsNoPropertyHooksOrInterfaceProperties(): void
    {
        $config = new GeneratorConfig(Generated::NAMESPACE, 'MusicCatalog', PhpTarget::Php83);
        $units = [...(new ReadModelEmitter())->emit(Fixtures::musicCatalogDescriptor(), $config)];

        self::assertNotSame([], $units);
        foreach ($units as $unit) {
            if ($unit instanceof ClassUnit) {
                self::assertStringNotContainsString('{ get', $unit->source, $unit->name . ' carries 8.4 property syntax');
            }
        }
        self::assertStringContainsString('{ get => $this->title(); }', Generated::file('resource-albums.php'), 'the 8.4 target keeps them');
    }

    public function testComposedAttributesFollowTheirKeywordsSemantics(): void
    {
        $releases = Generated::file('resource-releases.php');

        self::assertStringContainsString('public function format(): ReleaseFormatVinyl|ReleaseFormatCd|ReleaseFormatDigital|null', $releases, 'oneOf + discriminator is a union of branch classes, the null branch nullability');
        self::assertStringContainsString("public const string MEDIUM = 'vinyl';", $releases);
        self::assertStringContainsString('@phpstan-type ReleaseAvailabilityShape array{worldwide?: bool, regions?: list<string>}', $releases, 'anyOf merges with every member optional');
        self::assertStringContainsString('@phpstan-type ReleaseDimensionsShape array{widthMm: float, heightMm: float, depthMm?: float}', $releases, 'allOf flattens and keeps required members');
    }

    public function testAnAttributeNamedLikeAWriteFactoryIsReadThroughAFallback(): void
    {
        $emitter = new ReadModelEmitter();
        $units = [...$emitter->emit($this->widgets(['create' => new AttributeDescriptor('create', 'string', null, false, null)]), Generated::config())];

        $base = self::unit($units, 'WidgetBase');
        self::assertStringContainsString('public function createAttribute(): string', $base);
        self::assertStringContainsString("return \$this->_string('create');", $base);
        self::assertStringNotContainsString('function create()', $base);
        self::assertStringContainsString('public static function create(', self::unit($units, 'Widget'));
        self::assertSame(['"create" on widgets collides with the static Widget::create(); it is read through createAttribute() instead.'], $emitter->warnings());
    }

    public function testAnObjectAttributeNamedLikeAnotherEmittersAliasGetsItsOwn(): void
    {
        $schema = Fixtures::musicCatalogDescriptor()->resource('albums')?->attributes['releaseInfo']->schema;
        $units = [...(new ReadModelEmitter())->emit($this->widgets(['filter' => new AttributeDescriptor('filter', 'object', null, true, $schema)]), Generated::config())];

        $tags = [];
        foreach ($units as $unit) {
            if ($unit instanceof Contribution) {
                $tags = [...$tags, ...$unit->tags];
            }
        }
        self::assertSame(['@phpstan-type WidgetFilterAttributeShape array{label?: string, catalogueNumber?: string}'], $tags);
    }

    /**
     * @param array<string, AttributeDescriptor> $attributes
     */
    private function widgets(array $attributes): ApiDescriptor
    {
        $resource = new ResourceDescriptor(
            'widgets',
            $attributes,
            [],
            [],
            [],
            [OperationKind::Create->value => new OperationDescriptor('/widgets', 'POST', [], null)],
            new PaginatorDescriptor(PaginatorKind::None, []),
            ClientIdPolicy::Forbidden,
            null,
            [],
            [],
            [],
            [],
        );

        return new ApiDescriptor(['widgets' => $resource], [], null, 2, []);
    }

    /**
     * @param array<ClassUnit|Contribution> $units
     */
    private static function unit(array $units, string $name): string
    {
        foreach ($units as $unit) {
            if ($unit instanceof ClassUnit && $unit->name === $name) {
                return $unit->source;
            }
        }

        self::fail('No unit named ' . $name);
    }
}
