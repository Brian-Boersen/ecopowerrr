<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\HttpClient\HttpClient;

//


use App\Repository\DevicesRepository;
use App\Repository\CustomerRepository;
use App\Repository\MothlyYieldRepository;
use App\Entity\Customer;
use Symfony\Component\Serializer\Encoder\JsonDecode;

class SolarDataCollectorService
{
    private $retryClient;

    private $ItarableDate;
    private $presentDate;
    
    private $FakeDeviceData;

    private $reachedEnd = false;
    
    private $addedDevices = 0;
    private $completedDevices = 0;

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

        $this->ItarableDate = new \DateTime();
        $this->presentDate = 
        [
            $this->ItarableDate->format('Y'),
            $this->ItarableDate->format('m'),
            $this->ItarableDate->format('d')
        ];

        $this->FakeDeviceData = $this->fetchDataFile("./public/test_data/solar-data.json");
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

    public function CreateFakeDevice(Customer $customer)
    {
        $panelSerial = rand(10000000000,99999999999);

        $currentDate = new \DateTime();
        $totalSolarpanels = rand(5,20);
        
        $newForgedData = 
        [
            "device_id" => $panelSerial,

            "device_status" => "active",
            "date" => $currentDate->format('d/m/Y'),
            "type" => $totalSolarpanels
        ];
          

        for($i = 0; $i < $totalSolarpanels; $i++)
        {
            $totalYield = rand(100000,900000) / 100;
            $totalSurplus = rand(100000,$totalYield) / 100;
            $ranYield = rand(50,250);
            $ranSurplus = rand(0,$ranYield);

            $panelSerial = rand(10000000000,99999999999);

            $newForgedData['devices'][] = 
            [
                "serial_number" => $panelSerial,
                "device_type" => "solar",
                "device_status" => "active",
                "device_total_yield" =>$totalYield ."kWh",
                "device_month_yield" => $ranYield ."kWh",
                "device_month_surplus" => $ranSurplus ."kWh",
                "device_total_surpuls" => $totalSurplus ."kWh"
            ];
        }

        // dd($newForgedData);

        $newDevice = $this->devicesRepository->save($newForgedData, $customer);
        $this->mothlyYieldRepository->save($newForgedData, $newDevice);
    }

    // public function reducePanels($data)
    // {
    //     $newData = [];

    //     $addNew = true;

    //     foreach($data as $newDevice)
    //     {
    //         foreach($newData as $device)
    //         {
    //             if($device['device_id'] == $newDevice['device_id'])
    //             {
    //                 $addNew = false;
    //             }
    //         }

    //         if($addNew == true)
    //         {
    //             $newData[] = $newDevice;
    //         }

    //         $addNew = true;
    //     }

    //     return $newData;
    // }

    public function ReadAllMultiple()
    {
        $Devices = $this->devicesRepository->findAll(); 

        $GetheredData = [];
        
        $data = $this->FakeDeviceData;
        
        $lastDevice = end($Devices)->getSerialNumber();
        $devicesCount = count($Devices);
        $devicesAdded = 0;

        //dont put higher than 100 or it will exaust the memory
        $flushAfter = 10;

        $flush = false;
        
        foreach($Devices as $device)
        {
            print_r("\n". $device->getId());

            $panels = $this->mothlyYieldRepository->findBy(['device' => $device->getId()]);

            if(count($panels) > 0)
            {
                print_r("\n paneles not found");
                die();
            }

            array_filter($panels, "self::reducePanels");

            if(count($panels) > 0)
            {
                print_r("\n paneles not filterd");
                die();
            }

            $flush = false;

            $SN = $device->getSerialNumber();

            $devicesAdded++;
            
            if($devicesAdded >= $flushAfter)//$SN == $lastDevice)
            {
                $flush = true;
                $devicesAdded = 0;
                print_r("\n flushing !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
            }

            $deviceData = $data[array_search($SN, array_column($data, 'device_id'))];
            
            if($deviceData == null)
            {
                continue;
            }

            print_r("\n". $deviceData['device_id']);
            
            while($this->reachedEnd == false)
            {    
                $GetheredData[] = $deviceData;
                
                $newForgedData = 
                    [
                        "device_status" => "active",
                        "date" => $this->iterateDate(1),

                        "device_id" => $SN,
                        "type" => $device['type']
                    ];
                        

                foreach($deviceData['devices'] as $panel)
                {
                    $totalYield = rand(100000,1000000) / 100;
                    $totalSurplus = rand(100000,$totalYield) / 100;
                    $ranYield = rand(50,250);
                    $ranSurplus = rand(0,$ranYield);

                    $newForgedData['devices'][] = 
                    [
                        "serial_number" => $panel['serial_number'],
                        "device_type" => "solar",
                        "device_status" => "active",
                        "device_total_yield" =>$totalYield ."kWh",
                        "device_month_yield" => $ranYield ."kWh",
                        "device_month_surplus" => $ranSurplus ."kWh",
                        "device_total_surpuls" => $totalSurplus ."kWh"
                    ];
                }
                // dd($newForgedData);
                // die();

                if($this->reachedEnd == true && $flush == true)
                {
                    $this->mothlyYieldRepository->save($newForgedData, $device, false);      
                }
                else
                {
                    $this->mothlyYieldRepository->save($newForgedData, $device, $flush);      
                }

                $this->addedDevices++;
                // print_r("\n date: ". $newForgedData['date']);
            }  
            
            $this->completedDevices++;

            $this->reachedEnd = false;

            print_r("\n". $this->completedDevices .") added device with id: ".$SN);
        }

        return $GetheredData;
    }

    private function iterateDate($months,$resetDate = "20130501")
    {
        $newdate = $this->ItarableDate->format('d/m/Y');

        $this->ItarableDate->modify('-'.$months.' month');

        $resetDate += 100;
        $checkDate = $this->ItarableDate->format('Ymd');

        if($resetDate >= $checkDate)
        {
            $this->reachedEnd = true;
            $this->ItarableDate->setDate($this->presentDate[0],$this->presentDate[1],$this->presentDate[2]);
        }
        
        return $newdate;
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

    private function fetchDataFile($filePath)
    {
        $file = fopen($filePath, "r") or die("Unable to open file!");
        $fileData = fread($file,filesize($filePath));
        fclose($file);

        $data = json_decode($fileData, true);

        return $data;
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