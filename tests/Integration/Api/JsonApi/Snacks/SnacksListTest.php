<?php

declare(strict_types=1);

namespace Tab\Tests\Integration\Api\JsonApi\Snacks;

use Tab\Application\Schema\SnackSchema;
use Tab\Packages\Constants\HttpStatusCodes;
use Tab\Packages\TestCase\Mother\Entity\SnackMother;
use Tab\Packages\TestCase\Mother\Entity\UserMother;
use Tab\Tests\TestCase\JsonApiIntegrationTestCase;

/** @internal */
final class SnacksListTest extends JsonApiIntegrationTestCase
{
    public function test_not_logged_user_cannot_access_snacks_list(): void
    {
        // Arrange
        $snack = SnackMother::random();
        $this->loadEntities($snack);
        $jsonApiClient = $this->jsonApiClient(
            SnackSchema::class,
            $this->client(),
        );

        // Act
        $jsonApiResponse = $jsonApiClient->requestList();

        // Assert
        self::assertSame(
            HttpStatusCodes::HTTP_UNAUTHORIZED,
            $jsonApiResponse->statusCode(),
        );
    }

    public function test_logged_user_can_access_snacks_list(): void
    {
        // Arrange
        $snack = SnackMother::random();
        $loggedUser = UserMother::random();
        $this->loadEntities(
            $loggedUser,
            $snack,
        );
        $jsonApiClient = $this->loggedJsonApiClient(
            SnackSchema::class,
            $loggedUser,
        );

        // Act
        $jsonApiResponse = $jsonApiClient->requestList();

        // Assert
        $jsonApiDocument = $jsonApiResponse->document();
        self::assertSame(
            HttpStatusCodes::HTTP_OK,
            $jsonApiResponse->statusCode(),
        );
        $this->assertJsonApiType(SnackSchema::TYPE, $jsonApiDocument);
        $this->assertJsonApiItemsCount(1, $jsonApiDocument);
        $this->assertJsonApiIds(
            [$snack->id],
            $jsonApiDocument,
        );
        $resource = $jsonApiDocument->resourceAt(0);
        $this->assertJsonApiAttributes(
            $resource,
            [
                'name' => $snack->name,
            ],
        );
    }

    public function test_snacks_list_can_be_filtered(): void
    {
        // Arrange
        $snack1 = SnackMother::random();
        $snack2 = SnackMother::random();
        $loggedUser = UserMother::random();
        $this->loadEntities(
            $loggedUser,
            $snack1,
            $snack2,
        );
        $jsonApiClient = $this->loggedJsonApiClient(
            SnackSchema::class,
            $loggedUser,
        );

        // Act
        $jsonApiResponse = $jsonApiClient->requestList(
            additionalQueryParams: [
                'filter' => [
                    'name' => $snack2->name,
                ],
            ],
        );

        // Assert
        $jsonApiDocument = $jsonApiResponse->document();
        self::assertSame(
            HttpStatusCodes::HTTP_OK,
            $jsonApiResponse->statusCode(),
        );
        $this->assertJsonApiType(SnackSchema::TYPE, $jsonApiDocument);
        $this->assertJsonApiItemsCount(1, $jsonApiDocument);
        $this->assertJsonApiIds(
            [$snack2->id],
            $jsonApiDocument,
        );
        $resource = $jsonApiDocument->resourceAt(0);
        $this->assertJsonApiAttributes(
            $resource,
            [
                'name' => $snack2->name,
            ],
        );
    }
}