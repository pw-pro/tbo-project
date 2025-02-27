<?php

declare(strict_types=1);

namespace Polsl\Tests\Unit\Domain\Model\User;

use Polsl\Domain\DomainException;
use Polsl\Domain\Model\User\Password;
use Polsl\Packages\Faker\Faker;
use Polsl\Packages\TestCase\UnitTestCase;
use Polsl\Tests\TestCase\Application\Mock\FakePasswordHasher;

/**
 * @internal
 */
final class PasswordTest extends UnitTestCase
{
    public function test_password_can_be_created(): void
    {
        // Arrange
        $passwordHasher = FakePasswordHasher::getInstance();
        $password = Faker::password();

        // Expect
        self::expectNotToPerformAssertions();

        // Act
        Password::hash($password, $passwordHasher);
    }

    /**
     * @dataProvider invalidPasswordGenerator
     *
     * @param \Closure(): string $passwordGenerator
     */
    public function test_password_must_meet_conditions(
        \Closure $passwordGenerator,
    ): void {
        // Arrange
        $passwordHasher = FakePasswordHasher::getInstance();
        $password = $passwordGenerator();
        $passwordLength = \mb_strlen($password);
        $exceptionMessage = "Password length must be between 8 and 64 characters. Given password has {$passwordLength} characters.";

        // Expect
        self::expectException(DomainException::class);
        self::expectExceptionMessage($exceptionMessage);

        // Act
        Password::hash($password, $passwordHasher);
    }

    /** @return iterable<string, array{\Closure}> */
    public static function invalidPasswordGenerator(): iterable
    {
        yield 'too short password' => [
            static fn (): string => Faker::hexBytes(7),
        ];

        yield 'too long password' => [
            static fn (): string => Faker::hexBytes(65),
        ];
    }
}
