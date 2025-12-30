<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }
    /**
     * @param string $mode 'all' or 'following'
     * @return list<Post>
     */
    public function findSmartFeedPosts(?\App\Entity\User $user, string $mode = 'all', int $limit = 50): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'a')
            ->addSelect('a')
            ->leftJoin('p.comments', 'c')
            ->addSelect('c')
            ->leftJoin('p.postLikes', 'l')
            ->addSelect('l')
            ->andWhere('p.showInFeed = :val')
            ->setParameter('val', true)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit);

        if ($mode === 'following' && $user) {
            // Complex Logic:
            // 1. Posts from followed users
            // 2. OR Posts that are "Emergencies" (isMissing / isEmergency [future])
            // 3. OR Posts from "Organization" accounts (Shelters)

            // Note: Since we are in SQL/DQL, we need to check relationships.
            // Following check: p.author IN (:following)

            $following = $user->getFollowing();

            // If user follows no one, we still show emergencies/shelters, or maybe just a message? 
            // Better to show emergencies/shelters as requested.

            $orX = $qb->expr()->orX();

            // 1. Followed Users AND Me
            if (count($following) > 0) {
                $orX->add($qb->expr()->in('p.author', ':following'));
                $qb->setParameter('following', $following);
            }
            // Always include my own posts
            $orX->add($qb->expr()->eq('p.author', ':me'));
            $qb->setParameter('me', $user);

            // 2. Organization Accounts (Shelters)
            // Assuming accountType is 'organization' or similar. 
            // Checking entity field: accountType
            $orX->add($qb->expr()->eq('a.accountType', ':orgType'));
            $qb->setParameter('orgType', 'commercial'); // Usage of 'commercial' for now based on Registration form, ideally 'organization'

            // 3. Emergencies (Future fields, for now we assume all posts are normal, but if we had isEmergency)
            // $orX->add($qb->expr()->eq('p.isEmergency', true));

            $qb->andWhere($orX);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return list<Post>
     */
    public function findProfilePosts(\App\Entity\User $author, int $limit = 50): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'a')
            ->addSelect('a')
            ->leftJoin('p.comments', 'c')
            ->addSelect('c')
            ->leftJoin('p.postLikes', 'l')
            ->addSelect('l')
            ->andWhere('p.author = :author')
            ->setParameter('author', $author)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
