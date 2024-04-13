<?php

namespace App\Repository;

use App\Entity\PunishmentPoints;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PunishmentPoints>
 *
 * @method PunishmentPoints|null find($id, $lockMode = null, $lockVersion = null)
 * @method PunishmentPoints|null findOneBy(array $criteria, array $orderBy = null)
 * @method PunishmentPoints[]    findAll()
 * @method PunishmentPoints[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PunishmentPointsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PunishmentPoints::class);
    }

    //    /**
    //     * @return PunishmentPoints[] Returns an array of PunishmentPoints objects
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

    //    public function findOneBySomeField($value): ?PunishmentPoints
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
