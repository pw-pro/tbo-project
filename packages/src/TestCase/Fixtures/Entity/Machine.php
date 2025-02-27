<?php

declare(strict_types=1);

namespace Polsl\Packages\TestCase\Fixtures\Entity;

final readonly class Machine
{
    public function __construct(
        public int $id,
        public string $location,
        public int $positionNo,
        public int $positionCapacity,
    ) {}
}
