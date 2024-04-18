<?php

namespace App\Repository;

use App\Entity\RaceResultBet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RaceResultBet>
 *
 * @method RaceResultBet|null find($id, $lockMode = null, $lockVersion = null)
 * @method RaceResultBet|null findOneBy(array $criteria, array $orderBy = null)
 * @method RaceResultBet[]    findAll()
 * @method RaceResultBet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RaceResultBetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RaceResultBet::class);
    }

    //    /**
    //     * @return RaceResultBet[] Returns an array of RaceResultBet objects
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

    //    public function findOneBySomeField($value): ?RaceResultBet
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
