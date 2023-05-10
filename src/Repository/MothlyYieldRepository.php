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
        $splitDate = explode("/", $data['date']);

        $deviceDate = new \DateTime($splitDate[2].'-'.$splitDate[1].'-'.$splitDate[0]);
        
        $deviceEndDate = new \DateTime($splitDate[2].'-'.$splitDate[1].'-'.$splitDate[0]);
        $deviceEndDate->modify('+1 year');
        
        $deviceDays =  $deviceDate->format('j');
        $deviceDate->modify('-'.($deviceDays - 1).' days');

        foreach($data['devices'] as $device)
        {
            $devicesMonthlyYield = $this->findBy(['serial_number' => $device['serial_number']]);

            // print_r("date: ".$data['date'] . "\n");
            // print_r($splitDate[0].'-'.$splitDate[1].'-'.$splitDate[2] . "\n");
            // print_r("device date: " . $deviceDate->format('Y-m-d') . "\n");
            // print_r("device end date: " . $deviceEndDate->format('Y-m-d') . "\n");

            foreach($devicesMonthlyYield as $deviceMonthlyYield)
            {
                // print_r("db_Device date: " . $deviceMonthlyYield->getStartDate()->format('Y-m-d') . "\n");
                if($deviceMonthlyYield->getStartDate()->format('Y-m-d') == $deviceDate->format('Y-m-d'))
                {
                    print_r("failed at itaration" . "\n");
                    continue;
                }
            }

            print_r("device id: " . $dev->getId() . "\n");

            $entity = new MothlyYield();

            $entity->setDevice($dev);
            $entity->setSerialNumber(($device['serial_number']));

            $entity->setYield(floatval($device['device_month_yield']));
            $entity->setSurplus(floatval($device['device_month_surplus']));
            
            $entity->setStartDate($deviceDate);
            $entity->setEndDate($deviceEndDate);

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
