<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;

use App\Repository\RoutingRepository;
use Doctrine\ORM\Mapping as ORM;

use App\Controller\RegisterCustomerController;

use app\Entity\Customer;


#[ORM\Entity(repositoryClass: RoutingRepository::class)]
#[ApiResource(operations: [new POST(
    name: 'postTest',
    uriTemplate: '/add_customer',
    read: true,
    controller: RegisterCustomerController::class
)])]
class Routing
{
}
