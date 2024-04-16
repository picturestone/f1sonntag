<?php

namespace App\Repository;

use App\Entity\Driver;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Driver>
 *
 * @method Driver|null find($id, $lockMode = null, $lockVersion = null)
 * @method Driver|null findOneBy(array $criteria, array $orderBy = null)
 * @method Driver[]    findAll()
 * @method Driver[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DriverRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Driver::class);
    }

    /**
     * @return Driver[] Returns an array of Driver objects ordered by isActive DESC and lastName ASC.
     */
    public function findAllOrderByIsActiveAndLastName(): array
    {
        return $this->createQueryBuilder('d')
            ->addOrderBy('d.isActive', 'DESC')
            ->addOrderBy('d.lastName', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Driver[] Returns an array of Driver objects.
     */
    public function findActiveDrivers(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.isActive = :isActive')
            ->setParameter('isActive', true)
            ->getQuery()
            ->getResult()
            ;
    }

    //    /**
    //     * @return Driver[] Returns an array of Driver objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Driver
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
