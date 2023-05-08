<?php

namespace App\Controller;


use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;


class RegisterCustomerController extends AbstractController
{
    public function saveCustomer(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        
        // $customer = new Customer();
        // $customer->setFirstName($data['first_name']);
        // $customer->setLastName($data['last_name']);
        // $customer->setEmail($data['email']);
        // $customer->setPhonenumber($data['phonenumber']);
        // $customer->setPostcode($data['postcode']);
        // $customer->setCity($data['city']);
        // $customer->setStreet($data['street']);
        // $customer->setHouseNumber($data['house_number']);
        // $customer->setBankAccount($data['bank_account']);

        // $this->customer->save($customer); 
        
        return new Response("return: " . $request->getContent());
    }
}
