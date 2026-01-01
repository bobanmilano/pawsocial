<?php

/**
 * -------------------------------------------------------------
 * Developed by Boban Milanovic BSc <boban.milanovic@gmail.com>
 *
 * Project: PawSocial Social Network
 * Description: A social network platform designed for pets, animal lovers,
 * animal shelters, and organizations to connect, share, and collaborate.
 *
 * This software is proprietary and confidential. Any use, reproduction, or
 * distribution without explicit written permission from the author is strictly prohibited.
 *
 * For licensing or collaboration inquiries, please contact:
 * Email: boban.milanovic@gmail.com
 * -------------------------------------------------------------
 *
 * Class: UserRepository
 * Description: Repository class for finding User entities.
 * Responsibilities:
 * - Provides methods to query the database for User objects.
 * - Handles password upgrades.
 * -------------------------------------------------------------
 */



namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

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

    /**
     * @return User[]
     */
    public function searchUsersAndPets(string $query): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.animalProfile', 'a')
            ->addSelect('a')
            ->andWhere('u.firstName LIKE :query OR u.lastName LIKE :query OR u.email LIKE :query OR a.name LIKE :query OR a.breed LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('u.firstName', 'ASC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    // /**
    // * @return User[] Returns an array of User objects
    // */
    // public function findByExampleField($value): array
    // {
    // return $this->createQueryBuilder('u')
    // ->andWhere('u.exampleField = :val')
    // ->setParameter('val', $value)
    // ->orderBy('u.id', 'ASC')
    // ->setMaxResults(10)
    // ->getQuery()
    // ->getResult()
    // ;
    // }

    // public function findOneBySomeField($value): ?User
    // {
    // return $this->createQueryBuilder('u')
    // ->andWhere('u.exampleField = :val')
    // ->setParameter('val', $value)
    // ->getQuery()
    // ->getOneOrNullResult()
    // ;
    // }
}