<?php

namespace App\Repository;

use App\Entity\Devices;
use App\Entity\MothlyYield;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MothlyYield>
 *
 * @method MothlyYield|null find($id, $lockMode = null, $lockVersion = null)
 * @method MothlyYield|null findOneBy(array $criteria, array $orderBy = null)
 * @method MothlyYield[]    findAll()
 * @method MothlyYield[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MothlyYieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MothlyYield::class);
    }

    public function save(array $data,Devices $dev, bool $flush = true)
    {
        $devicesMonthlyYield = $this->findBy([
            'device' => $dev]);
            
        foreach($data['devices'] as $device)
        {
            $entity = new MothlyYield();

            $entity->setDevice($dev);
            $entity->setSerialNumber(($device['serial_number']));

            $entity->setYield(floatval($device['device_month_yield']));
            $entity->setSurplus(floatval($device['device_month_surplus']));

            $deviceDate = new \DateTime($data['date']);
            $deviceDays =  $deviceDate->format('j');
            $deviceMonth = $deviceDate->format('m');
            
            $entity->setEndDate($deviceDate);
            $entity->setStartDate($deviceDate->modify('-'.($deviceDays - 1).' days'));

            if($devicesMonthlyYield != null)
            {
                foreach($devicesMonthlyYield as $deviceMonthYield )
                {
                    $storedDeviceMonth = $deviceMonthYield->getStartDate()->format('m');
                    
                    if($storedDeviceMonth == $deviceMonth)
                    {   
                        $entity->setStartDate($deviceMonthYield->getStartDate());
                    }
                }
            }
            
            $this->getEntityManager()->persist($entity);

            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }
    }

    public function remove(MothlyYield $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MothlyYield[] Returns an array of MothlyYield objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MothlyYield
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
