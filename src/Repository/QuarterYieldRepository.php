<?php

namespace App\Repository;

use App\Entity\QuarterYield;
use App\Repository\YearlyYieldRepository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuarterYield>
 *
 * @method QuarterYield|null find($id, $lockMode = null, $lockVersion = null)
 * @method QuarterYield|null findOneBy(array $criteria, array $orderBy = null)
 * @method QuarterYield[]    findAll()
 * @method QuarterYield[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuarterYieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private YearlyYieldRepository $yearlyYieldRepository)
    {
        parent::__construct($registry, QuarterYield::class);
    }

    public function save($device,$yield,$startDate,$lastYear, bool $flush = false): void
    {
        $floorYear = new \DateTime($lastYear->format('Y-m-d'));
        $floorYear->modify('-1 year');
        
        if($startDate->format('ym') <= $floorYear->format('ym'))
        {
            $this->yearlyYieldRepository->save($device,$yield,$startDate,$floorYear,$flush);
            return;
        }
        
        $devicesQuarterlyYield = $this->findBy(['serial_number' => $yield['serial_number']]);

        if($devicesQuarterlyYield != null)
        {
            foreach($devicesQuarterlyYield as $deviceQuarterlyYield)
            {
                $dbStartDate = $deviceQuarterlyYield->getStartDate()->format('ym');
                $dbEndDate = $deviceQuarterlyYield->getEndDate()->format('ym');

                if
                (
                    $startDate->format('ym') >= $dbStartDate &&
                    $startDate->format('ym') <= $dbEndDate
                )
                {
                    $deviceQuarterlyYield->setYield($deviceQuarterlyYield->getYield() + floatval($yield['device_month_yield']));
                    $deviceQuarterlyYield->setSurplus($deviceQuarterlyYield->getSurplus() + floatval($yield['device_month_surplus']));

                    $this->getEntityManager()->persist($deviceQuarterlyYield);

                    if ($flush)
                    {
                        $this->getEntityManager()->flush();
                    }

                    return;
                }
            }            
        }

        $quarterEndDate = new \DateTime($lastYear->format('Y-m-d'));
        $quarterStartDate = new \DateTime($lastYear->format('Y-m-d'));

        for($i = 0; $i < 4; $i++)
        {
            $quarterStartDate->modify('-3 month');

            if
            (
                $startDate->format('ym') >= $quarterStartDate->format('ym') &&
                $startDate->format('ym') <= $quarterEndDate->format('ym')
            )
            {
                $entity = $this->makeEntety($device,$yield,$quarterStartDate,$quarterEndDate);

                $this->getEntityManager()->persist($entity);

                if ($flush) {
                    $this->getEntityManager()->flush();
                }
                return;
            }

            $quarterEndDate->modify('-3 month');
        }
    }

    private function makeEntety($device,$yield,$deviceDate,$deviceEndDate): QuarterYield
    {
        $entity = new QuarterYield();

        $entity->setDevice($device);
        $entity->setSerialNumber($yield['serial_number']);

        $entity->setYield(floatval($yield['device_month_yield']));
        $entity->setSurplus(floatval($yield['device_month_surplus']));
        
        $entity->setStartDate($deviceDate);
        $entity->setEndDate($deviceEndDate);

        //$this->getEntityManager()->persist($entity);
        return $entity;
    }

    public function remove(QuarterYield $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return QuarterYield[] Returns an array of QuarterYield objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('q.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?QuarterYield
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
