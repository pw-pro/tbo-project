<?php

declare(strict_types=1);

namespace Polsl\Infrastructure\Doctrine\Fields;

use Polsl\Application\Schema\SnackSchema;
use Polsl\Application\View\SnackView;
use Polsl\Packages\Constants\Database\Tables;
use Polsl\Packages\ResourcesList\Fields;
use Polsl\Packages\SqlExpressions\JsonObject;

final class SnackFieldsFactory
{
    private const DIRECT_ATTRIBUTES = [
        SnackSchema::ATTRIBUTE_NAME => [
            SnackView::FIELD_RAW_NAME,
            Tables\Snacks::FIELD_NAME,
        ],
    ];

    public function create(
        string $snacksTableAlias,
        Fields $typeFields,
    ): JsonObject {
        $snackFields = new JsonObject();
        $snackFields->addField(
            SnackView::FIELD_RAW_ID,
            "{$snacksTableAlias}.snack_id",
        );
        if (false === $typeFields->hasType(SnackSchema::TYPE)) {
            return $snackFields;
        }

        $snackTypeFields = $typeFields->typeFields(
            SnackSchema::TYPE,
        );
        $this->addDirectAttributes(
            $snackFields,
            $snackTypeFields,
            $snacksTableAlias,
        );
        $this->addQuantity(
            $snackFields,
            $snackTypeFields,
            $snacksTableAlias,
        );

        return $snackFields;
    }

    private function addDirectAttributes(
        JsonObject $snackFields,
        Fields\TypeFields $snackTypeFields,
        string $tableAlias,
    ): void {
        foreach (self::DIRECT_ATTRIBUTES as $fieldName => $columns) {
            [
                $jsonColumn,
                $databaseColumn,
            ] = $columns;

            if (false === $snackTypeFields->hasField((string) $fieldName)) {
                continue;
            }

            $snackFields->addField(
                $jsonColumn,
                "{$tableAlias}.{$databaseColumn}",
            );
        }
    }

    private function addQuantity(
        JsonObject $snackFields,
        Fields\TypeFields $snackTypeFields,
        string $tableAlias,
    ): void {
        if (false === $snackTypeFields->hasField(SnackSchema::ATTRIBUTE_QUANTITY)) {
            return;
        }

        $quantitySql = <<<SQL
            (
                SELECT w.quantity
                FROM warehouse_snacks w
                WHERE w.snack_id = {$tableAlias}.snack_id
                LIMIT 1
            )
            SQL
        ;
        $snackFields->addField(SnackView::FIELD_RAW_QUANTITY, $quantitySql);
    }
}
