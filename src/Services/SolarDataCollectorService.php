<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\HttpClient\HttpClient;


use App\Repository\DevicesRepository;
use App\Repository\CustomerRepository;
use App\Repository\MothlyYieldRepository;
use App\Entity\Customer;
use Symfony\Component\Serializer\Encoder\JsonDecode;

class SolarDataCollectorService
{
    private $retryClient;

    public function __construct
    (
        private HttpClientInterface $client,
        private DevicesRepository $devicesRepository,
        private CustomerRepository $customerRepository,
        private Customer $customer,
        private MothlyYieldRepository $mothlyYieldRepository
        )
    {
        $this->retryClient = new RetryableHttpClient(HttpClient::create());
    }
    
    public function ReadNewDevice(Customer $customer)
    {
        $data = $this->FetchData();

        $data = $data[0];

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

    public function ReadOne($sn)
    {
        $res = $this->FetchData($sn);

        if(count($res) == 0)
        {
            return null;
        }

        $data = $res[0];

        print_r(json_encode($data));

        return $data;
    }

    private $addedDevices = 0;
    private $completedDevices = 0;

    public function ReadAllMultiple($amountPerDevice = 1)
    {
        $Devices = $this->devicesRepository->findAll(); 
        $GetheredData = [];

        $lastDevice = end($Devices)->getSerialNumber();
        $devicesCount = count($Devices);
        $devicesAdded = 0;
        $flushAfter = 1;

        $flush = false;

        foreach($Devices as $device)
        {
            $flush = false;

            $SN = $device->getSerialNumber();

            $devicesAdded++;

            if($devicesAdded >= $flushAfter)//$SN == $lastDevice)
            {
                $flush = true;
                $devicesAdded = 0;
                print_r("\n flushing !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
            }

            for($i = 0; $i < $amountPerDevice; $i++)
            {    
                $data = $this->FetchData($SN,"/x");
                
                if($data == null)
                {
                    continue;
                }

                $data = $data[0];
                $GetheredData[] = $data;

                if($i < $amountPerDevice - 1)
                {
                    $this->mothlyYieldRepository->save($data, $device, false);      
                }
                else
                {
                    $this->mothlyYieldRepository->save($data, $device, $flush);      
                }

                $this->addedDevices++;
                print_r("\n". $this->addedDevices);
            }

            $this->completedDevices++;

            print_r("\n". $this->completedDevices .") added device with id: ".$SN);
        }

        return $GetheredData;
    }

    public function ReadAllDevices()
    {
        $Devices = $this->devicesRepository->findAll(); 

        foreach($Devices as $device)
        {
            $data = $this->FetchData($device->getSerialNumber());
            
            if($data == null)
            {
                continue;
            }

            $data = $data[0];

            $this->mothlyYieldRepository->save($data, $device);          
        }
    }

    private $connections = 0;

    private function FetchData($id = "", $x = "")
    {
        ($id == "")? $id = "":$id = "/$id";

        $response =  $this->retryClient->request(
            'GET',
            "http://localhost:70/fetch_data$x$id"
        );
        
        $this->connections++;
        $data = $response->toArray();
 
        $response->cancel();

        return $data;
    }

    // #[Route('/colect-data/{id}', name:'Solar-Data-Collecter', methods: ['GET'])]
    // public function ReadDevices($id,Request $request):Response
    // {
    //     $response = $this->client->request(
    //         'GET',
    //         'http://localhost:70/fetch_data'
    //     ); 

    //     $data = $response->toArray();

    //     $deviceData = [
    //         'device_id' => $data[0]['device_id'],
    //         'device_status' => $data[0]['device_status'],
    //         'customer' => 1,
    //         'type' => count($data[0]['devices'])
    //     ];

    //     $this->devicesRepository->save($deviceData);
        
    //     return new Response(json_encode($deviceData));
    // }
}