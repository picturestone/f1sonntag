<?php

namespace App\Repository;

use App\Entity\PositionBet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PositionBet>
 *
 * @method PositionBet|null find($id, $lockMode = null, $lockVersion = null)
 * @method PositionBet|null findOneBy(array $criteria, array $orderBy = null)
 * @method PositionBet[]    findAll()
 * @method PositionBet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PositionBetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PositionBet::class);
    }

    //    /**
    //     * @return PositionBet[] Returns an array of PositionBet objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PositionBet
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
