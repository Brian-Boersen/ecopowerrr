<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\Repository\CustomerRepository;
use App\Repository\ContractRepository;

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