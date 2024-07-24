<?php

namespace App\Repository;

use App\Entity\Annonce;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Annonce>
 */
class AnnonceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Annonce::class);
    }

    //    /**
    //     * @return Annonce[] Returns an array of Annonce objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Annonce
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function rechercheAnnonce(?string $keyword, ?string $ville, ?int $minPrix, ?int $maxPrix)
    {
        $qb = $this->createQueryBuilder('a');

        if ($keyword) {
            $qb->andWhere('a.titre LIKE :keyword OR a.description LIKE :keyword OR a.ville LIKE :keyword')
                ->setParameter('keyword', '%' . $keyword . '%');
        }

        if ($ville) {
            $qb->andWhere('a.ville LIKE :ville')
                ->setParameter('ville', '%' . $ville . '%');
        }

        if ($minPrix !== null) {
            $qb->andWhere('a.prix >= :minPrix')
                ->setParameter('minPrix', $minPrix);
        }

        if ($maxPrix !== null) {
            $qb->andWhere('a.prix <= :maxPrix')
                ->setParameter('maxPrix', $maxPrix);
        }

        return $qb->orderBy('a.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}