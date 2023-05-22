<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 *
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function __invoke(Customer $data){
        return $data;
    }

    public function save(array $data, bool $flush = true): Customer
    {   
        $entity =  new Customer();
        $entity->setFirstName($data['firstName']);
        $entity->setLastName($data['lastName']);

        $entity->setEmail($data['email']);
        $entity->setPhonenumber($data['phonenumber']);

        $entity->setPostcode($data['postcode']);
        $entity->setCity($data['city']);
        $entity->setProvince($data['province']);

        $entity->setStreet($data['street']);
        $entity->setHouseNumber($data['houseNumber']);

        $entity->setBankAccount($data['bankAccount']);

        $entity->setMunicipality($data['municipality']);

        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

        return $entity;
    }

    public function remove(Customer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function postTest(){
        return "test";
    }

   /**
    * @return Customer[] Returns an array of Customer objects
    */
   public function findByExampleField($value,$field = ""): array
   {
       return $this->createQueryBuilder('c')
           ->andWhere('c.'.$field.' = :val')
           ->setParameter('val', $value)
           ->orderBy('c.id', 'ASC')
           ->setMaxResults(10)
           ->getQuery()
           ->getResult()
       ;
   }

//    public function findOneBySomeField($value): ?Customer
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
