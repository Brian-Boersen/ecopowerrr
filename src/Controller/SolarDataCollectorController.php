<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\Repository\DevicesRepository;
use App\Repository\CustomerRepository;
use App\Repository\MothlyYieldRepository;
use App\Entity\Customer;
use App\Entity\MothlyYield;

#[Route('/solar')]
class SolarDataCollectorController extends AbstractController
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
        $response = $this->client->request(
            'GET',
            'http://localhost:70/fetch_data'
        ); 

        $data = $response->toArray();

        $deviceData = [
            'device_id' => $data[0]['device_id'],
            'device_status' => $data[0]['device_status'],
            'customer' => $customer,
            'type' => count($data[0]['devices'])
        ];

        $newDevice =  $this->devicesRepository->save($deviceData, $customer);

        $this->mothlyYieldRepository->save($data[0], $newDevice);    

        return $data;

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
