<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tab\Packages\Constants\Database\Tables;

final class CreateMachineSnackTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table(
            Tables::MACHINE_SNACKS,
            [
                'id' => Tables\MachineSnack::FIELD_ID,
            ],
        );
        $table
            ->addColumn(
                Tables\MachineSnack::FIELD_SNACK_ID,
                'integer',
                [
                    'null' => false,
                ],
            )
            ->addColumn(
                Tables\MachineSnack::FIELD_MACHINE_ID,
                'integer',
                [
                    'null' => false,
                ],
            )
            ->addColumn(
                Tables\MachineSnack::FIELD_QUANTITY,
                'integer',
                [
                    'null' => false,
                ],
            )
            ->addColumn(
                Tables\MachineSnack::FIELD_POSITION,
                'string',
                [
                    'null' => false,
                    'limit' => 3,
                ],
            )
            ->addForeignKey(
                Tables\MachineSnack::FIELD_MACHINE_ID,
                Tables::MACHINES,
                Tables\Machines::FIELD_MACHINE_ID,
                ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'],
            )
            ->addForeignKey(
                Tables\MachineSnack::FIELD_SNACK_ID,
                Tables::SNACKS,
                Tables\Snacks::FIELD_SNACK_ID,
                ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'],
            )
        ;
        $table->create();
    }
}