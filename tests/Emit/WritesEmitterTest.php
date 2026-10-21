<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ClientIdPolicy;
use haddowg\JsonApiCodegen\Descriptor\ResourceDescriptor;
use haddowg\JsonApiCodegen\Descriptor\WriteAttributeDescriptor;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Writes\CreateSource;
use haddowg\JsonApiCodegen\Emit\Writes\Shape;
use haddowg\JsonApiCodegen\Emit\Writes\UpdateSource;
use haddowg\JsonApiCodegen\Emit\Writes\WriteModel;
use haddowg\JsonApiCodegen\Emit\Writes\WritesEmitter;
use haddowg\JsonApiCodegen\Emit\Writes\WriteSource;
use haddowg\JsonApiCodegen\Tests\Support\ArtefactParity;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WritesEmitter::class)]
#[CoversClass(WriteModel::class)]
#[CoversClass(WriteSource::class)]
#[CoversClass(CreateSource::class)]
#[CoversClass(UpdateSource::class)]
#[CoversClass(Shape::class)]
final class WritesEmitterTest extends TestCase
{
    public function testAlbumCreateMatchesTheArtefact(): void
    {
        ArtefactParity::assertMatches('AlbumCreate', 'resource-albums.php', Generated::file('resource-albums.php'));
    }

    public function testAlbumCreateBuilderMatchesTheArtefact(): void
    {
        ArtefactParity::assertMatches('AlbumCreateBuilder', 'resource-albums.php', Generated::file('resource-albums.php'));
    }

    public function testAlbumUpdateMatchesTheArtefact(): void
    {
        ArtefactParity::assertMatches('AlbumUpdate', 'resource-albums.php', Generated::file('resource-albums.php'));
    }

    public function testTheResourceClassCarriesTheArtefactsWriteAliasesInItsOrder(): void
    {
        $aliases = ['AlbumStatusInput', 'AlbumDateInput', 'AlbumArtistInput', 'AlbumTracksInput', 'AlbumCreateShape', 'AlbumUpdateShape'];
        $artefact = self::aliasLines(self::artefact(), $aliases);

        self::assertSame(\array_keys($artefact), $aliases, 'the artefact declares the aliases in this order');
        self::assertSame($artefact, self::aliasLines(Generated::file('resource-albums.php'), $aliases));
    }

    public function testTheResourceClassCarriesTheArtefactsWriteConstants(): void
    {
        foreach (['WRITABLE', 'REQUIRED_ON_CREATE'] as $constant) {
            self::assertSame(
                self::constant(self::artefact(), $constant),
                self::constant(Generated::file('resource-albums.php'), $constant),
                $constant,
            );
        }
    }

    public function testThePhp83TargetEmitsTheSameWriteClasses(): void
    {
        $api = Fixtures::musicCatalogDescriptor();
        $on84 = self::sources($api, PhpTarget::Php84);

        self::assertNotSame([], $on84);
        self::assertSame($on84, self::sources($api, PhpTarget::Php83), 'write inputs carry no property hooks, so nothing differs by target');
    }

    public function testEachClassExistsOnlyWhereItsOperationDoes(): void
    {
        $names = \array_keys(self::sources(Fixtures::musicCatalogDescriptor(), PhpTarget::Php84));

        self::assertContains('CatalogExportCreate', $names);
        self::assertContains('CatalogExportCreateBuilder', $names);
        self::assertNotContains('CatalogExportUpdate', $names, 'catalog-exports has no update operation');
        self::assertNotContains('ChartCreate', $names, 'charts is read-only');
        self::assertNotContains('ChartUpdate', $names);
    }

    public function testAForbiddenClientIdTakesNoIdParameter(): void
    {
        self::assertStringNotContainsString('$id', self::sources(Fixtures::musicCatalogDescriptor(), PhpTarget::Php84)['AlbumCreate']);
    }

    public function testARequiredClientIdIsARequiredParameter(): void
    {
        $source = self::sources(Fixtures::musicCatalogDescriptor(), PhpTarget::Php84)['GenreCreate'];

        self::assertStringContainsString("public readonly string \$id,\n", $source);
        self::assertStringContainsString("'id' => \$this->id", $source);
    }

    public function testAnOptionalClientIdIsANullableParameterSentOnlyWhenGiven(): void
    {
        $api = self::withResource(Fixtures::musicCatalogDescriptor(), 'genres', clientId: ClientIdPolicy::Optional);
        $sources = self::sources($api, PhpTarget::Php84);

        self::assertStringContainsString('public readonly ?string $id = null,', $sources['GenreCreate']);
        self::assertStringContainsString("if (\$this->id !== null) {\n            \$data['id'] = \$this->id;", $sources['GenreCreate']);
        self::assertStringContainsString('private ?string $id = null;', $sources['GenreCreateBuilder']);
        self::assertStringNotContainsString("['id']", $sources['GenreCreateBuilder'], 'an optional id is not guarded');
    }

    public function testAMemberNamedLikeAWriteMethodGetsAPrefixedSetter(): void
    {
        $api = Fixtures::musicCatalogDescriptor();
        $devices = $api->resource('devices');
        self::assertNotNull($devices);
        $label = $devices->updateAttributes['label'];
        $build = new WriteAttributeDescriptor('build', $label->format, null, false, null, false);
        $api = self::withResource($api, 'devices', updateAttributes: ['build' => $build, 'label' => $label]);

        $source = self::sources($api, PhpTarget::Php84)['DeviceUpdate'];

        self::assertStringContainsString('public function setBuild(string $build): self', $source);
        self::assertStringContainsString('public function build(): self', $source);
        self::assertStringContainsString('build: $build,', $source, 'the named argument keeps the member name');
    }

    private static function artefact(): string
    {
        $source = \file_get_contents(ArtefactParity::DIRECTORY . '/resource-albums.php');
        self::assertIsString($source);

        return $source;
    }

    /**
     * @param list<string> $aliases
     *
     * @return array<string, string> each alias's declaration line, in the order the source declares them
     */
    private static function aliasLines(string $source, array $aliases): array
    {
        \preg_match_all('/@phpstan-type (\w+) .*$/m', $source, $matches, \PREG_SET_ORDER);
        $out = [];
        foreach ($matches as $match) {
            if (\in_array($match[1], $aliases, true) && !isset($out[$match[1]])) {
                $out[$match[1]] = $match[0];
            }
        }

        return $out;
    }

    private static function constant(string $source, string $name): string
    {
        self::assertSame(1, \preg_match('/public const array ' . $name . ' = \[[^;]*\];/', $source, $match), $name);

        return (string) \preg_replace('/\s+/', '', $match[0]);
    }

    /**
     * @return array<string, string> every write class's source, by class name
     */
    private static function sources(ApiDescriptor $api, PhpTarget $target): array
    {
        $out = [];
        foreach ((new WritesEmitter())->emit($api, new GeneratorConfig(Generated::NAMESPACE, 'MusicCatalog', $target)) as $unit) {
            if ($unit instanceof ClassUnit) {
                $out[$unit->name] = $unit->source;
            }
        }

        return $out;
    }

    /**
     * @param array<string, WriteAttributeDescriptor>|null $updateAttributes
     */
    private static function withResource(ApiDescriptor $api, string $type, ?ClientIdPolicy $clientId = null, ?array $updateAttributes = null): ApiDescriptor
    {
        $r = $api->resource($type);
        self::assertNotNull($r);

        $resources = $api->resources;
        $resources[$type] = new ResourceDescriptor(
            $r->type,
            $r->attributes,
            $r->createAttributes,
            $updateAttributes ?? $r->updateAttributes,
            $r->relations,
            $r->operations,
            $r->paginator,
            $clientId ?? $r->clientId,
            $r->countable,
            $r->includable,
            $r->sortable,
            $r->filterable,
            $r->actions,
            $r->createRelations,
            $r->updateRelations,
            $r->memberOrder,
        );

        return new ApiDescriptor($resources, $api->enums, $api->atomic, $api->contract, $api->errors);
    }
}
