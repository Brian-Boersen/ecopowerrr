<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\Services\SolarDataCollectorService;

#[Route('/devices')]
class SolarDataCollectorController extends AbstractController
{

    public function __construct
    (
        private SolarDataCollectorService $solarService
    )
    {}
    
    #[Route('/get', name:'customer_save_data', methods: ['GET'])] 
    public function saveData(Request $request): Response
    {
        $result = $this->solarService->ReadAllDevices();

        if($result == null)
        {
            return new Response(json_encode(['error' => 'reading all devices could not be completed']));
        }
        
        return new Response(json_encode($result));
    }
}
