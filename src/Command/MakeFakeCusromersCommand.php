<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// use App\Services\SolarDataCollectorService;
use App\Services\CustomerRegistrationService;

#[AsCommand(
    name: 'make:fake:Customers',
    description: 'Add a short description for your command',
)]
class MakeFakeCusromersCommand extends Command
{
    public function __construct
    (
        private CustomerRegistrationService $customerRegistrationService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        // $this
        //     ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        //     ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        // ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $completed = $this->customerRegistrationService->makeFakeCustomers();

        if($completed != null)
        {
            $io->success("Fake customers created \n" . count($completed));
            return Command::SUCCESS;
        }
        else
        {
            $io->error('Fake customers not created');
            return Command::FAILURE;
        }
    }
}
