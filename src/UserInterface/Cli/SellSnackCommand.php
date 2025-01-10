<?php

declare(strict_types=1);

namespace Polsl\UserInterface\Cli;

use Doctrine\DBAL\Connection;
use Polsl\Application\Command\SellSnack\SellSnack;
use Polsl\Domain\Model\Machine\SnackPosition;
use Polsl\Packages\MessageBus\Contracts\CommandBusInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SellSnackCommand extends Command
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private Connection $dbConnection,
    ) {
        parent::__construct('app:snacks:sell');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Simulate snack selling.')
            ->addArgument('machineId', InputArgument::REQUIRED)
            ->addArgument('snackId', InputArgument::REQUIRED)
            ->addArgument('position', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $machineId = $input->getArgument('machineId');
        $snackId = $input->getArgument('snackId');
        $position = $input->getArgument('position');

        $this->dbConnection
            ->executeQuery(
                "SELECT * FROM machines WHERE id = {$machineId}",
            )
        ;

        if (!\is_numeric($machineId) || !\is_numeric($snackId)) {
            $output->writeln('<error>MachineId or SnackId is not numeric</error>');

            return 1;
        }
        $positionVo = SnackPosition::fromString($position);

        $sellSnack = new SellSnack(
            (int) $machineId,
            (int) $snackId,
            $positionVo->toString(),
        );
        $this->commandBus
            ->handle($sellSnack)
        ;

        $output->writeln('<info>Success</info>');

        return 0;
    }
}
