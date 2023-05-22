<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Services\YearlyRevenueAnalyticService;

#[AsCommand(
    name: 'analytics:yearly:revenue:spreadsheet',
    description: 'get a preadsheet with the yearly revenue of all customers',
)]
class AnalyticsYearlyRevenueSpreadsheetCommand extends Command
{
    public function __construct
    (
        private YearlyRevenueAnalyticService $analyticsService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
        ->setHelp('gives a spreadsheet of total revenue of this year and a trendline based on past results')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $cus_ov = $this->analyticsService->yearlyRevenue();

        $io->success($cus_ov);

        return Command::SUCCESS;
    }
}
