<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    // Une methode pour calculer la moyenne(rating) 
    public function getAverageRating(User $user): ?float
    {
        $qb = $this->createQueryBuilder('u')
            ->select('AVG(r.rating) as avgRating')
            ->leftJoin('u.ratings', 'r')
            ->where('u.id = :user')
            ->setParameter('user', $user->getId())
            ->getQuery();

        return $qb->getSingleScalarResult();  
    }

    // Bloquer ou debloquer l'utilisateur
    public function blockUser(User $user): void
    {
        $user->setBlocked(true);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function unblockUser(User $user): void
    {
        $user->setBlocked(false);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function updateNewMessages(User $user) 
    {
        $user->setNouveauxMessages(null);
        
    } 

    // Methode d'anonymisation
    public function anonymizeUser(User $user): void
    {
        $randomString = bin2hex(random_bytes(5)); //Genere random de string
        $user->setNom('Anonyme');
        $user->setEmail('anonyme_'. $randomString . '@example.com');
        $user->setBlocked(true); // Optionnel : bloquer l'utilisateur
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
