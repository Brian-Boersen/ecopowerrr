<?php

namespace App\Service;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\GetCollection;
use SebastianBergmann\Environment\Console;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\Response;

#[ApiResource(operations: [
    new Get(),
    new Post(
        name: 'getTest', 
        uriTemplate: '/users/test'
    )
])]
class ApiGateService
{
    public function getTest(){
        
        return json_encode("test");
    }
}
