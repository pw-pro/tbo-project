<?php

declare(strict_types=1);

namespace Polsl\Packages\TestCase\Fixtures\Entity;

final class WarehouseSnacks
{
    public function __construct(
        public int $id,
        public int $quantity,
    ) {}
}
