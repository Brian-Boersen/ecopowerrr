<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Services\SolarDataCollectorService;

use Psr\Log\LoggerInterface;

#[AsCommand(
    name: 'fetch:all:solar:data',
    description: 'Add a short description for your command',
)]
class FetchAllSolarDataCommand extends Command
{

    public function __construct
    (
        private SolarDataCollectorService $solarDataService,
        private LoggerInterface $logger
    )
    {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->setHelp('Fetch all data from Customer smart meters')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->logger->info('Fetching data' . date(' Y-m-d H:i:s'));

        $io->success('Fetched data');

        $result = $this->solarDataService->ReadAllDevices();

        return Command::SUCCESS;
    }
}
