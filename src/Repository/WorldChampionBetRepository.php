<?php

namespace App\Repository;

use App\Entity\WorldChampionBet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorldChampionBet>
 *
 * @method WorldChampionBet|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorldChampionBet|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorldChampionBet[]    findAll()
 * @method WorldChampionBet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorldChampionBetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorldChampionBet::class);
    }

    //    /**
    //     * @return WorldChampionBet[] Returns an array of WorldChampionBet objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?WorldChampionBet
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
