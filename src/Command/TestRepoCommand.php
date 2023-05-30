<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Repository\QuarterYieldRepository;
use App\Repository\DevicesRepository;

use App\Services\SolarDataCollectorService;

#[AsCommand(
    name: 'collect:solar:history',
    description: 'Add a short description for your command',
)]
class TestRepoCommand extends Command
{
    public function __construct
    (
        private QuarterYieldRepository $quarterYieldRepository,
        private DevicesRepository $DevicesRepository,
        private SolarDataCollectorService $solarDataService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $retext = "";

        //dont forget to uncomment storing in the db
        $datas = $this->solarDataService->ReadAllMultiple();

        (is_array($datas) == true) ?$retext .= "\n count: " . count($datas). "\n":"";
       
        if($datas == null)
        {
            $io->error("no data found");
            return Command::FAILURE;
        }

        $io->success("succes, dates are: /n" .$retext);
        return Command::SUCCESS;
    }

    // protected function execute(InputInterface $input, OutputInterface $output): int
    // {
    //     $io = new SymfonyStyle($input, $output);
        
    //     $device = $this->DevicesRepository->find(2);

    //     $data = json_decode
    //     ('{
    //         "serial_number":"1135282051",
    //         "device_type":"solar",
    //         "device_status":"active",
    //         "device_total_yield":"239.22kWh",
    //         "device_month_yield":"42.21kWh",
    //         "device_total_surpuls":"117.22kWh",
    //         "device_month_surplus":"69.04kWh"
    //     }',
    //     true
    //     );

    //     $lastYear = new \DateTime();
    //     $lastYear->modify('-1 year');

    //     $yieldDate = new \DateTime($lastYear->format('Y-m-d'));
    //     $yieldDate->modify('-25 month');

    //     print_r("
    //              lastYear: " . $lastYear->format('Y-m-d')."
    //              yieldDate: " . $yieldDate->format('Y-m-d')."
    //             ");


    //     $this->quarterYieldRepository->save($device, $data, $yieldDate, $lastYear, true);

    //     $io->success("You have a new command! Now make it your own! Pass --help to see your options.");

    //     return Command::SUCCESS;
    // }
}
