<?php

namespace App\Services;

use App\Repository\ContractRepository;
use App\Repository\CustomerRepository;

use App\Entity\Customer;

use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\Services\SolarDataCollectorService;

class ConnectionService {


    public function __construct
    (
        private CustomerRepository $customerRepository,
        private ContractRepository $contractRepository,
        private HttpClientInterface $client,
        private SolarDataCollectorService $solarDataService
    ){}

    public function clientConnect($data,$fake = false)
    {
        file_put_contents('client.txt', print_r($data, true));

        /// save customer
        $newCustomer = $this->saveCustomer($data);

        /// create contract 
        $this->createContract($data,$newCustomer);

        if($fake == true)
        {
            $this->CreateDevice($newCustomer);
            return;
        }
        
        /// set status 
        $this->SetStatus();

        //// read devices
        $this->ReadDevices($newCustomer);

        return;
    }

    private function saveCustomer($data) 
    {
        return $this->customerRepository->save($data);
    }

    private function createContract($data,$newCustomer) 
    {
        $this->contractRepository->save($data,$newCustomer);
    }

    private function SetStatus($status = true)
    {
        $url = 'http://localhost:70/setstatus/active';

        if($status == false)
        {
            $url = 'http://localhost:70/setstatus/inactive';   
        }

        $this->client->request(
            'GET',
            $url
        ); 
    }

    private function ReadDevices(Customer $customer)
    {
        return $this->solarDataService->ReadNewDevice($customer);
    }

    private function CreateDevice(Customer $customer)
    {
        return $this->solarDataService->CreateFakeDevice($customer);
    }
}