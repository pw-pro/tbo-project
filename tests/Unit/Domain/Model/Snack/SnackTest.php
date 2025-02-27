<?php

declare(strict_types=1);

namespace Polsl\Tests\Unit\Domain\Model\Snack;

use Polsl\Domain\DomainException;
use Polsl\Domain\Model\Snack\Name;
use Polsl\Domain\Model\Snack\Snack;
use Polsl\Packages\Faker\Faker;
use Polsl\Packages\TestCase\UnitTestCase;
use Polsl\Tests\TestCase\Application\Mother\SnackMother;

/**
 * @internal
 */
final class SnackTest extends UnitTestCase
{
    public function test_snack_can_be_created(): void
    {
        // Arrange
        $name = Name::fromString(Faker::text());

        // Expect
        self::expectNotToPerformAssertions();

        // Act
        Snack::create($name);
    }

    public function test_name_can_be_changed(): void
    {
        // Arrange
        $snack = SnackMother::random();
        $name = Name::fromString(
            Faker::text(),
        );

        // Expect
        self::expectNotToPerformAssertions();

        // Act
        $snack->changeName($name);
    }

    public function test_cannot_add_quantity_without_warehouse_snack(): void
    {
        // Arrange
        $snack = SnackMother::createWithoutWarehouseSnack();

        // Expect
        self::expectException(DomainException::class);
        self::expectExceptionMessage('Cannot find warehouse snack');

        // Act
        $snack->addWarehouseQuantity(Faker::int(min: 0));
    }

    public function test_can_add_warehouse_quantity(): void
    {
        // Arrange
        $snack = SnackMother::random();

        // Expect
        self::expectNotToPerformAssertions();

        // Act
        $snack->addWarehouseQuantity(Faker::int(min: 1));
    }

    public function test_cannot_decrease_quantity_without_warehouse_snack(): void
    {
        // Arrange
        $snack = SnackMother::createWithoutWarehouseSnack();

        // Expect
        self::expectException(DomainException::class);
        self::expectExceptionMessage('Cannot find warehouse snack');

        // Act
        $snack->decreaseWarehouseQuantity(Faker::int(min: 0));
    }

    public function test_can_decrease_warehouse_quantity(): void
    {
        // Arrange
        $snack = SnackMother::random();

        // Expect
        self::expectNotToPerformAssertions();

        // Act
        $snack->decreaseWarehouseQuantity(Faker::int(min: 1, max: 100));
    }
}
