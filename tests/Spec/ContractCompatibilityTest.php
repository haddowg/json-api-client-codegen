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
        self::assertSame(8, Fixtures::musicCatalog()->contract());
        self::assertNull(ContractCompatibility::check(Fixtures::musicCatalog()->contract()));
    }

    public function testASupportedContractSaysNothing(): void
    {
        self::assertNull(ContractCompatibility::check(ContractCompatibility::MINIMUM));
        self::assertNull(ContractCompatibility::check(ContractCompatibility::MAXIMUM));
    }

    public function testContractEightIsReadAndNineIsNot(): void
    {
        // Contract 8 splits pivot schemas, keys relation paths by URI field name and marks
        // write-only relations, all of which this codegen reads; whatever 9 adds, it does not.
        self::assertNull(ContractCompatibility::check(7));
        self::assertNull(ContractCompatibility::check(8));

        $warning = ContractCompatibility::check(9);
        self::assertSame('the document declares contract 9 and this codegen reads up to 8. It describes capabilities that were not generated; upgrade the codegen to pick them up.', $warning);
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
