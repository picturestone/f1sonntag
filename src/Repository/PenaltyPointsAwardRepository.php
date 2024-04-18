<?php

namespace App\Repository;

use App\Entity\PenaltyPointsAward;
use App\Entity\Race;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PenaltyPointsAward>
 *
 * @method PenaltyPointsAward|null find($id, $lockMode = null, $lockVersion = null)
 * @method PenaltyPointsAward|null findOneBy(array $criteria, array $orderBy = null)
 * @method PenaltyPointsAward[]    findAll()
 * @method PenaltyPointsAward[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PenaltyPointsAwardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PenaltyPointsAward::class);
    }

    /**
     * @return PenaltyPointsAward[] Returns an array of PenaltyPointsAward objects.
     */
    public function findPenaltyPointsAwardsByRace(Race $race): array
    {
        return $this->createQueryBuilder('pp')
            ->where('pp.race = :race')
            ->setParameter('race', $race)
            ->getQuery()
            ->getResult()
            ;
    }

    //    /**
    //     * @return PenaltyPointsAward[] Returns an array of PenaltyPointsAward objects
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

    //    public function findOneBySomeField($value): ?PenaltyPointsAward
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
