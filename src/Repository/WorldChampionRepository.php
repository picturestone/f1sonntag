<?php

namespace App\Repository;

use App\Entity\WorldChampion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorldChampion>
 *
 * @method WorldChampion|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorldChampion|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorldChampion[]    findAll()
 * @method WorldChampion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorldChampionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorldChampion::class);
    }

    //    /**
    //     * @return WorldChampion[] Returns an array of WorldChampion objects
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

    //    public function findOneBySomeField($value): ?WorldChampion
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
