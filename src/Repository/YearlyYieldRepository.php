<?php

namespace App\Repository;

use App\Entity\YearlyYield;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<YearlyYield>
 *
 * @method YearlyYield|null find($id, $lockMode = null, $lockVersion = null)
 * @method YearlyYield|null findOneBy(array $criteria, array $orderBy = null)
 * @method YearlyYield[]    findAll()
 * @method YearlyYield[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class YearlyYieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, YearlyYield::class);
    }

    public function save($device,$yield,$startDate,$floorYear, bool $flush = false): void
    {   
        $devicesYearlyYield = $this->findBy(['serial_number' => $yield['serial_number']]);

        if($devicesYearlyYield != null)
        {
            foreach($devicesYearlyYield as $deviceYearrlyYield)
            {
                $dbStartDate = $deviceYearrlyYield->getStartDate()->format('ym');
                $dbEndDate = $deviceYearrlyYield->getEndDate()->format('ym');
  
                if
                (
                    $startDate->format('ym') >= $dbStartDate &&
                    $startDate->format('ym') <= $dbEndDate
                )
                {

                    $deviceYearrlyYield->setYield($deviceYearrlyYield->getYield() + floatval($yield['device_month_yield']));
                    $deviceYearrlyYield->setSurplus($deviceYearrlyYield->getSurplus() + floatval($yield['device_month_surplus']));

                    $this->getEntityManager()->persist($deviceYearrlyYield);

                    if ($flush) {
                        $this->getEntityManager()->flush();
                    }

                    return;
                }
            }            
        }

        $quarterEndDate = new \DateTime($floorYear->format('Y-m-d'));
        $quarterStartDate = new \DateTime($floorYear->format('Y-m-d'));

        while(true)
        {
            $quarterStartDate->modify('-12 month');

            if
            (
                $startDate->format('ym') >= $quarterStartDate->format('ym') &&
                $startDate->format('ym') <= $quarterEndDate->format('ym')
            )
            {
                $entity = $this->makeEntety($device,$yield,$quarterStartDate,$quarterEndDate);

                $this->getEntityManager()->persist($entity);

                if ($flush)
                {
                    $this->getEntityManager()->flush();
                }

                return;
            }

            $quarterEndDate->modify('-12 month');
        }
        
    }

    private function makeEntety($device,$yield,$deviceDate,$deviceEndDate): YearlyYield
    {
        $entity = new YearlyYield();

        $entity->setDevice($device);
        $entity->setSerialNumber($yield['serial_number']);

        $entity->setYield(floatval($yield['device_month_yield']));
        $entity->setSurplus(floatval($yield['device_month_surplus']));
        
        $entity->setStartDate($deviceDate);
        $entity->setEndDate($deviceEndDate);

        return $entity;
    }

    public function remove(YearlyYield $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return YearlyYield[] Returns an array of YearlyYield objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('y')
//            ->andWhere('y.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('y.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?YearlyYield
//    {
//        return $this->createQueryBuilder('y')
//            ->andWhere('y.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
