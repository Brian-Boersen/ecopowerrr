<?php

namespace App\Services;

use App\Repository\ContractRepository;
use App\Repository\CustomerRepository;
use App\Repository\DevicesRepository;
use App\Repository\MothlyYieldRepository;

use PhpOffice\PhpSpreadsheet\IOFactory;

class AnalyticsService
{
    //constructor
    public function __construct
    (
        private CustomerRepository $customerRepository,
        private ContractRepository $contractRepository,
        private DevicesRepository $deviceRepository,
        private MothlyYieldRepository $monthlyYieldRepository,
    ){}

    public function CustomerOverview()
    {

        $customers = $this->customerRepository->findAll();
        $contracts = $this->contractRepository->findAll();
        $devices = $this->deviceRepository->findAll();
        $monthlyYields = $this->monthlyYieldRepository->findAll();

        $overview = [
            'customers' => count($customers),
            'contracts' => count($contracts),
            'devices' => count($devices),
            'monthlyYields' => count($monthlyYields)
        ];

        return $overview;
    }
}

