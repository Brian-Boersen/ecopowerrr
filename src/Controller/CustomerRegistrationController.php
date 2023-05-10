<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Services\CustomerRegistrationService;

#[Route('/customer')]
class CustomerRegistrationController extends AbstractController
{
 
    private $cs;

    private string $token = '98661c0c-1239-4d6e-9391-3b90d99d82f5';

    //construct
    public function __construct
    (
        private CustomerRegistrationService $customerRegisionService
    )
    {
    }

    #[Route('/add', name:'customer_save_data', methods: ['POST'])] 
    public function saveData(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $result = $this->customerRegisionService->saveData($data);

        if($result == null)
        {
            return new Response(json_encode(['error' => 'invalid postcode or house number']));
        }
        
        return new Response(json_encode($result));
    } 
}