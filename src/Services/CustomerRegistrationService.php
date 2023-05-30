<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\Repository\CustomerRepository;
use App\Repository\ContractRepository;
use App\Repository\PcLatLongRepository;

use App\Controller\SolarDataCollectorController;

use App\Entity\Customer;

use App\Services\ConnectionService;
use App\Services\SolarDataCollectorService;

class CustomerRegistrationService
{
 
    private $cs;

    private string $token = '98661c0c-1239-4d6e-9391-3b90d99d82f5';

    //construct
    public function __construct
    (
        private SolarDataCollectorService $solarDataService,
        private CustomerRepository $customerRepository,
        private HttpClientInterface $client,
        private ContractRepository $contractRepository,
        private PcLatLongRepository $pcLatLongRepository,
        ConnectionService $cs
    )
    {
        $this->cs = $cs;
    }
    
    public function saveData(array $data)
    {
        $data['postcode'] = strtolower(str_replace(' ', '', $data['postcode']));
        $data['houseNumber'] = intval($data['houseNumber']);

        $adress = $this->GetAdress($data['postcode'],$data['houseNumber']);

        if($adress['city'] == null)
        {
            return null;
        }

        $data['city'] = $adress['city'];
        $data['street'] = $adress['street'];
        $data['province'] = $adress['province'];
        $data['municipality'] = $adress['municipality'];

        $result = $this->cs->clientConnect($data);

        return ($result);
    } 

    public function makeFakeCustomers()
    {
        $longLatData = $this->pcLatLongRepository->findAll();

        $customersData = [];

        foreach($longLatData as $LLData)
        {
            if($LLData->getPc6() == null || $LLData->getLat() == null || $LLData->getLng() == null)
            {
                continue;
            }

            for($i = 0; $i < 10; $i++)
            {
                $customerData = 
                [
                    'firstName' => 'jhon',
                    'lastName' => 'doe',

                    'email' => 'fake@mail.com',
                    'phonenumber' => '1234',

                    'contractStartDate' => '01/09/2010',
                    'contractEndDate' => '01/09/2023',
                    'buyPrice' => 143,
                    'sellPrice' => 17,

                    'postcode' => $LLData->getPc6(),

                    'street' => '',
                    'houseNumber' => rand(1,9999),
                    'city' => '',
                    'province' => '',
                    'municipality' => '',
                    'bankAccount' => 'Rabo 1234',
                    'lat' => $LLData->getLat(),
                    'long' => $LLData->getLng()
                ];

                $this->saveSpecialData($customerData);
                $customersData[] = $customerData;
            }
        }

        //$this->saveSpecialData($customerData);

        return $customersData;
    }

    private function saveSpecialData(array $data)
    {
        $data['postcode'] = strtolower(str_replace(' ', '', $data['postcode']));
        $data['houseNumber'] = intval($data['houseNumber']);

        $result = $this->cs->clientConnect($data,true);

        return ($result);
    } 
    
    private function GetAdress($postcode,$houseNumber):array
    {
        $response =  $this->client->request(
            'GET',
            'https://postcode.tech/api/v1/postcode/full?postcode='.$postcode.'&number='.$houseNumber,[
                'auth_bearer' => $this->token
            ]); 

        $data = $response->toArray();

        return $data;

    } 
}