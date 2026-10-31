<?php

declare(strict_types=1);

namespace haddowg\JsonApiCodegen\Tests\Spec;

use haddowg\JsonApiCodegen\Spec\ContractCompatibility;
use haddowg\JsonApiCodegen\Spec\UnsupportedContractException;
use haddowg\JsonApiCodegen\Tests\Support\Fixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContractCompatibility::class)]
#[CoversClass(UnsupportedContractException::class)]
final class ContractCompatibilityTest extends TestCase
{
    public function testAnAbsentContractIsNoSignalAndProceeds(): void
    {
        self::assertNull(ContractCompatibility::check(null));
    }

    public function testTheMusicCatalogContractIsOneThisCodegenReads(): void
    {
        self::assertSame(13, Fixtures::musicCatalog()->contract());
        self::assertNull(ContractCompatibility::check(Fixtures::musicCatalog()->contract()));
    }

    public function testASupportedContractSaysNothing(): void
    {
        self::assertNull(ContractCompatibility::check(ContractCompatibility::MINIMUM));
        self::assertNull(ContractCompatibility::check(ContractCompatibility::MAXIMUM));
    }

    public function testContractNineteenIsReadAndTwentyIsNot(): void
    {
        // Contract 10 lets an action answer in raw media types, 11 lets it accept several request
        // bodies, 12 adds operation examples, 13 an example detail on every error code, 14 the
        // links a type declares, 15 per-method action ids and declared action safety, and 16
        // operation scopes and one-member polymorphic unions, 17 an action's location document,
        // 18 shared links components, and 19 discriminated unions and a typed included, all of
        // which this codegen reads; whatever 20 adds, it does not.
        foreach (\range(9, 19) as $contract) {
            self::assertNull(ContractCompatibility::check($contract));
        }

        $warning = ContractCompatibility::check(20);
        self::assertSame('the document declares contract 20 and this codegen reads up to 19. It describes capabilities that were not generated; upgrade the codegen to pick them up.', $warning);
    }

    public function testANewerContractWarnsThatCapabilitiesWereNotGenerated(): void
    {
        $warning = ContractCompatibility::check(ContractCompatibility::MAXIMUM + 1);

        self::assertNotNull($warning);
        self::assertStringContainsString((string) (ContractCompatibility::MAXIMUM + 1), $warning);
        self::assertStringContainsString('not generated', $warning);
    }

    public function testAnOlderContractIsAnError(): void
    {
        $contract = ContractCompatibility::MINIMUM - 1;

        $this->expectException(UnsupportedContractException::class);
        $this->expectExceptionMessage('predates what this codegen reads');

        ContractCompatibility::check($contract);
    }

    public function testTheErrorCarriesTheRangeItChecked(): void
    {
        try {
            ContractCompatibility::check(ContractCompatibility::MINIMUM - 1);
            self::fail('expected the check to reject a contract below the supported minimum');
        } catch (UnsupportedContractException $e) {
            self::assertSame(ContractCompatibility::MINIMUM - 1, $e->contract);
            self::assertSame(ContractCompatibility::MINIMUM, $e->minimum);
            self::assertSame(ContractCompatibility::MAXIMUM, $e->maximum);
        }
    }
}
