<?php

declare(strict_types=1);

namespace Tab\Packages\Tests\ResourcesList;

use Tab\Packages\Faker\Faker;
use Tab\Packages\ResourcesList\TotalItems;
use Tab\Packages\TestCase\UnitTestCase;

/** @internal */
final class TotalItemsTest extends UnitTestCase
{
    public function test_total_items_cannot_be_lower_than_zero(): void
    {
        $wrongInt = Faker::int(\PHP_INT_MIN, -1);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Total items count cannot be lower than '0', '{$wrongInt}' passed.");

        TotalItems::fromInt($wrongInt);
    }

    public function test_total_items_can_be_represented_as_integer(): void
    {
        $totalItemsInt = Faker::int(0);
        $totalItems = TotalItems::fromInt($totalItemsInt);

        self::assertSame($totalItemsInt, $totalItems->toInt());
    }
}