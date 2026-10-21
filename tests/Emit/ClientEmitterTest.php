<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Emit;

use haddowg\JsonApiCodegen\Descriptor\ApiDescriptor;
use haddowg\JsonApiCodegen\Descriptor\ErrorCodeDescriptor;
use haddowg\JsonApiCodegen\Emit\Assembler;
use haddowg\JsonApiCodegen\Emit\ClassUnit;
use haddowg\JsonApiCodegen\Emit\GeneratorConfig;
use haddowg\JsonApiCodegen\Emit\Naming;
use haddowg\JsonApiCodegen\Emit\PhpTarget;
use haddowg\JsonApiCodegen\Emit\Root\ClientEmitter;
use haddowg\JsonApiCodegen\Emit\Root\ErrorCodeEmitter;
use haddowg\JsonApiCodegen\Tests\Support\ArtefactParity;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use haddowg\JsonApiCodegen\Tests\Support\Generated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClientEmitter::class)]
final class ClientEmitterTest extends TestCase
{
    public function testMusicCatalogClientMatchesTheArtefact(): void
    {
        // The artefact's client is trimmed to `albums`, so it is compared with a run over that type alone.
        $full = Fixtures::musicCatalogDescriptor();
        $albums = $full->resource('albums');
        self::assertNotNull($albums);
        $trimmed = new ApiDescriptor(['albums' => $albums], $full->enums, $full->atomic, $full->contract, $full->errors);

        $files = Assembler::assemble((new ClientEmitter())->emit($trimmed, Generated::config()), Generated::config(), 'x');

        ArtefactParity::assertMatches('MusicCatalogClient', 'client.php', $files['client.php']);
    }

    public function testTheFullRunHasOneAccessorPerType(): void
    {
        $client = Generated::file('client.php');

        foreach (\array_keys(Fixtures::musicCatalogDescriptor()->resources) as $type) {
            self::assertStringContainsString(\sprintf('public readonly %s $%s;', Naming::accessor($type), Naming::member($type)), $client);
        }
        self::assertStringContainsString('$this->publicProfiles = new PublicProfiles($fetcher);', $client);
        self::assertSame(\count(Fixtures::musicCatalogDescriptor()->resources), \substr_count($client, 'public readonly '));
    }

    public function testAtomicIsNotGeneratedAlthoughTheServerAdvertisesIt(): void
    {
        self::assertNotNull(Fixtures::musicCatalogDescriptor()->atomic);
        self::assertStringNotContainsString('atomic', Generated::file('client.php'));
    }

    public function testThe83TargetEmitsTheSameClient(): void
    {
        $api = Fixtures::musicCatalogDescriptor();
        $on83 = new GeneratorConfig(Generated::NAMESPACE, 'MusicCatalog', PhpTarget::Php83);

        self::assertEquals(
            [...(new ClientEmitter())->emit($api, Generated::config())],
            [...(new ClientEmitter())->emit($api, $on83)],
        );
    }

    public function testApplicationCodesAreHandedToTheTransport(): void
    {
        $api = new ApiDescriptor([], [], null, 2, [
            'TRACK_LOCKED' => new ErrorCodeDescriptor('TRACK_LOCKED', 409, 'TrackLockedError', 'Track is locked', null),
        ]);

        $unit = [...(new ClientEmitter(new ErrorCodeEmitter([])))->emit($api, Generated::config())][0];
        self::assertInstanceOf(ClassUnit::class, $unit);

        self::assertStringContainsString('$fetcher = new Fetcher(new Transport($options->withErrorCodes(MusicCatalogErrorCodes::MAP)));', $unit->source);
    }

    public function testCoreCodesLeaveTheTransportOptionsAlone(): void
    {
        $api = new ApiDescriptor([], [], null, 2, [
            'APPLICATION_ERROR' => new ErrorCodeDescriptor('APPLICATION_ERROR', 500, 'ApplicationError', 'Application error', null),
        ]);

        $unit = [...(new ClientEmitter(new ErrorCodeEmitter(['APPLICATION_ERROR'])))->emit($api, Generated::config())][0];
        self::assertInstanceOf(ClassUnit::class, $unit);

        self::assertStringContainsString('new Transport($options)', $unit->source);
    }
}
