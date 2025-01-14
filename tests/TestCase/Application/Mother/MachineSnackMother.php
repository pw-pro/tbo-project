<?php

declare(strict_types=1);

namespace Polsl\Tests\TestCase\Application\Mother;

use Polsl\Domain\Model\Machine\MachineSnack;
use Polsl\Domain\Model\Machine\SnackPosition;
use Polsl\Packages\Faker\Faker;
use Polsl\Tests\TestCase\Application\Mock\FakeClock;
use Polsl\Tests\TestCase\Application\PropertyAccess\PropertyManipulator;

final class MachineSnackMother
{
    public static function random(): MachineSnack
    {
        return self::create();
    }

    public static function withPosition(string $position): MachineSnack
    {
        return self::create($position);
    }

    public static function withQuantity(
        int $quantity,
        ?string $position = null,
    ): MachineSnack {
        $propertyManipulator = PropertyManipulator::getInstance();
        $snackMachine = self::create($position);
        $propertyManipulator->propertySet(
            $snackMachine,
            'quantity',
            $quantity,
        );

        return $snackMachine;
    }

    private static function create(
        ?string $position = null,
    ): MachineSnack {
        $machine = MachineMother::random();
        $snack = SnackMother::random();
        $positionVO = SnackPosition::fromString(
            $position ?? Faker::hexBytes(3),
        );

        return MachineSnack::create(
            $machine,
            $snack,
            Faker::int(1, 10),
            $positionVO,
            new FakeClock(),
        );
    }
}
