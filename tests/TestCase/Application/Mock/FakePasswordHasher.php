<?php

declare(strict_types=1);

namespace Tab\Tests\TestCase\Application\Mock;

use Tab\Packages\PasswordHasher\PasswordHasherInterface;

final class FakePasswordHasher implements PasswordHasherInterface
{
    private static ?self $instance = null;

    public function hash(string $plainPassword): string
    {
        return \hash('xxh128', $plainPassword);
    }

    public static function getInstance(): PasswordHasherInterface
    {
        return self::$instance ??= new self();
    }
}