<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\Repository\DevicesRepository;
use App\Repository\CustomerRepository;
use App\Repository\MothlyYieldRepository;
use App\Entity\Customer;
use Symfony\Component\Serializer\Encoder\JsonDecode;

class SolarDataCollectorService
{
    public function __construct
    (
        private HttpClientInterface $client,
        private DevicesRepository $devicesRepository,
        private CustomerRepository $customerRepository,
        private Customer $customer,
        private MothlyYieldRepository $mothlyYieldRepository
        )
    {}
    
    public function ReadNewDevice(Customer $customer)
    {
        $res = $this->FetchData();

        $data = $res->toArray()[0];

        $deviceData = [
            'device_id' => $data['device_id'],
            'device_status' => $data['device_status'],
            'customer' => $customer,
            'type' => count($data['devices'])
        ];
        
        $newDevice =  $this->devicesRepository->save($deviceData, $customer);

        $this->mothlyYieldRepository->save($data, $newDevice);    

        return $data;
    }

    private function FetchData($postcode = '1567bd',$num = '32')
    {
        return $this->client->request(
            'GET',
            "http://localhost:70/fetch_data/$postcode/$num"
        ); 
    }

    #[Route('/colect-data/{id}', name:'Solar-Data-Collecter', methods: ['GET'])]
    public function ReadDevices($id,Request $request):Response
    {
        $response = $this->client->request(
            'GET',
            'http://localhost:70/fetch_data'
        ); 

        $data = $response->toArray();

        $deviceData = [
            'device_id' => $data[0]['device_id'],
            'device_status' => $data[0]['device_status'],
            'customer' => 1,
            'type' => count($data[0]['devices'])
        ];

        $this->devicesRepository->save($deviceData);
        
        return new Response(json_encode($deviceData));
    }
}