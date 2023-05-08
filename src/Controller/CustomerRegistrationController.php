<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\Repository\CustomerRepository;
use App\Controller\SolarDataCollectorController;
use App\Entity\Customer;

#[Route('/customer')]
class CustomerRegistrationController extends AbstractController
{
    private $customerRepository;
    private $solarDataController;

    private string $token = '98661c0c-1239-4d6e-9391-3b90d99d82f5';

    //construct
    public function __construct
    (
        SolarDataCollectorController $solarDataController,
        CustomerRepository $customerRepository,
        private HttpClientInterface $client
    )
    {
        $this->customerRepository = $customerRepository;
        $this->solarDataController = $solarDataController;
    }
    
    #[Route('/s', name:'homepage_index')]
    public function geting(Request $request):Response
    {
        return new Response("return: " . $request->getContent());
    }

    #[Route('/add', name:'homepage_save_data', methods: ['POST'])] 
    public function saveData(Request $request): Response {
        //$params = $request->request->all();
        $data = json_decode($request->getContent(), true);


        $this->SetStatus();

        $data['postcode'] = strtolower(str_replace(' ', '', $data['postcode']));
        $data['houseNumber'] = intval($data['houseNumber']);

        $adress = $this->GetAdress($data['postcode'],$data['houseNumber']);

        $data['city'] = $adress['city'];
        $data['street'] = $adress['street'];

        $newCustomer = $this->customerRepository->save($data);

        $test = $this->ReadDevices($newCustomer);

        return new Response(json_encode($test));
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

    private function ReadDevices(Customer $customer)
    {
        return $this->solarDataController->ReadNewDevice($customer);
    }

}