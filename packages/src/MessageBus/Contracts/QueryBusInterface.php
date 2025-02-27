<?php

declare(strict_types=1);

namespace Polsl\Packages\MessageBus\Contracts;

interface QueryBusInterface
{
    /** @return mixed */
    public function handle(object $query);
}
