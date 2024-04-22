<?php

namespace App\Repository;

use App\Entity\Race;
use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Season>
 *
 * @method Season|null find($id, $lockMode = null, $lockVersion = null)
 * @method Season|null findOneBy(array $criteria, array $orderBy = null)
 * @method Season[]    findAll()
 * @method Season[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Season::class);
    }

    public function findSeasonWithSingleRaceDataForScores(int $seasonId, Race $race): ?Season
    {
        /** @var Season $season */
        $season = $this->createQueryBuilder('s')
            ->join('s.races', 'r')
            ->join('r.raceResults', 'rr')
            ->join('r.raceResultBets', 'rrb')
            ->join('r.penaltyPointsAwards', 'ppa')
            ->where('s.id = :seasonId')
            ->andWhere('r.id = :raceId')
            ->andWhere('rr.race = :race')
            ->andWhere('rrb.race = :race')
            ->andWhere('ppa.race = :race')
            ->setParameter('seasonId', $seasonId)
            ->setParameter('raceId', $race->getId())
            ->setParameter('race', $race)
            ->addSelect('r')
            ->addSelect('rr')
            ->addSelect('rrb')
            ->addSelect('ppa')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $season;
    }

    //    /**
    //     * @return Season[] Returns an array of Season objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Season
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
