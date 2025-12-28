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
 * Class: AnimalRepository
 * Description: Repository class for finding Animal entities.
 * Responsibilities:
 * - Provides methods to query the database for Animal objects.
 * -------------------------------------------------------------
 */



namespace App\Repository;

use App\Entity\Animal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Animal>
 */
class AnimalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Animal::class);
    }

    // /**
    // * @return Animal[] Returns an array of Animal objects
    // */
    // public function findByExampleField($value): array
    // {
    // return $this->createQueryBuilder('a')
    // ->andWhere('a.exampleField = :val')
    // ->setParameter('val', $value)
    // ->orderBy('a.id', 'ASC')
    // ->setMaxResults(10)
    // ->getQuery()
    // ->getResult()
    // ;
    // }

    // public function findOneBySomeField($value): ?Animal
    // {
    // return $this->createQueryBuilder('a')
    // ->andWhere('a.exampleField = :val')
    // ->setParameter('val', $value)
    // ->getQuery()
    // ->getOneOrNullResult()
    // ;
    // }
}