<?php

declare(strict_types=1);

namespace Tab\Application\View;

/**
 * @phpstan-import-type MachineSnackData from MachineSnackView
 */
final readonly class MachineView
{
    public const FIELD_RAW_ID = 'id';
    public const FIELD_RAW_LOCATION = 'location';
    public const FIELD_RAW_POSITIONS_NUMBER = 'positions_no';
    public const FIELD_RAW_POSITIONS_CAPACITY = 'positions_capacity';
    public const FIELD_RAW_MACHINE_SNACKS = 'machine_snacks';

    /** @param MachineSnackView[] $machineSnacks */
    private function __construct(
        public int $id,
        public string $location,
        public int $positionsNo,
        public int $positionsCapacity,
        public array $machineSnacks,
    ) {
    }

    /**
     * @param array{
     *     id?: int,
     *     location?: string,
     *     positions_no?: int,
     *     positions_capacity?: int,
     *     machine_snacks?: list<MachineSnackData>,
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data[self::FIELD_RAW_ID] ?? 0,
            $data[self::FIELD_RAW_LOCATION] ?? '',
            $data[self::FIELD_RAW_POSITIONS_NUMBER] ?? 0,
            $data[self::FIELD_RAW_POSITIONS_CAPACITY] ?? 0,
            \array_map(
                static fn (array $machineSnackData): MachineSnackView => MachineSnackView::fromArray($machineSnackData),
                $data[self::FIELD_RAW_MACHINE_SNACKS] ?? [],
            ),
        );
    }
}
