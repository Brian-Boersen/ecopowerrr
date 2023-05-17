<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Services\AnalyticsService;

#[AsCommand(
    name: 'analytics:customer:spreadsheet',
    description: 'Add a short description for your command',
)]
class AnalyticsCustomerSpreadsheetCommand extends Command
{
    public function __construct
    (
        private AnalyticsService $analyticsService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('gives a spreadsheet of all customers and their data')
            ->addArgument('timeframe', InputArgument::OPTIONAL, 'timeframe of data, use: (m, q or y), represents (month, quarter or year)')   
            // ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if(!$input->getArgument('timeframe'))
        {
            $io->error('please enter a timeframe in the form of (m, q or y) for (month, quarter or year)');
            return Command::FAILURE;
        }

        $timeframe = $input->getArgument('timeframe');

        $cus_ov = $this->analyticsService->customerOverview($timeframe);

        $io->success($cus_ov);

        return Command::SUCCESS;
    }
}
