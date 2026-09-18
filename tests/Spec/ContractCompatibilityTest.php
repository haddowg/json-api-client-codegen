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
        self::assertNull(ContractCompatibility::check(Fixtures::musicCatalog()->contract()));
    }

    public function testASupportedContractSaysNothing(): void
    {
        self::assertNull(ContractCompatibility::check(ContractCompatibility::MINIMUM));
        self::assertNull(ContractCompatibility::check(ContractCompatibility::MAXIMUM));
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
