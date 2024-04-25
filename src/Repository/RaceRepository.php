<?php

namespace App\Repository;

use App\Entity\Race;
use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Race>
 *
 * @method Race|null find($id, $lockMode = null, $lockVersion = null)
 * @method Race|null findOneBy(array $criteria, array $orderBy = null)
 * @method Race[]    findAll()
 * @method Race[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Race::class);
    }

    /**
     * @return Race[] Returns an array of Race objects.
     */
    public function findRacesBySeason(Season $season): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.season = :season')
            ->setParameter('season', $season)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Race[] Returns an array of Race objects.
     */
    public function findRacesBySeasonOrderByStartDateAndStartTime(Season $season): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.season = :season')
            ->setParameter('season', $season)
            ->addOrderBy('r.startDateTime', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Race[] Returns an array of Race objects.
     */
    public function findRacesOfActiveSeason(): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.season', 's')
            ->where('s.isActive = :isActive')
            ->setParameter('isActive', true)
            ->getQuery()
            ->getResult()
            ;
    }

    //    public function findOneBySomeField($value): ?Race
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
