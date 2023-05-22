<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Services\MunicipalityAnalyticService;

#[AsCommand(
    name: 'analytics:municipality:spreadsheet',
    description: 'gives total revenue, yield and surplus per municipality',
)]
class AnalyticsMunicipalitySpreadsheetCommand extends Command
{
    public function __construct
    (
        private MunicipalityAnalyticService $analyticsService
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

        $cus_ov = $this->analyticsService->municipalityOverview();

        $io->success($cus_ov);

        return Command::SUCCESS;
    }
}
