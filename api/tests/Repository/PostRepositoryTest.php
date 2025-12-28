<?php

namespace App\Tests\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PostRepositoryTest extends KernelTestCase
{
    private ?\Doctrine\ORM\EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testSearchByFeedVisibility(): void
    {
        // Helper to separate runs
        $unique = uniqid();

        $user = new User();
        $user->setEmail('repo_user_' . $unique . '@example.com');
        $user->setPassword('password');
        $user->setFirstName('Repo');
        $this->entityManager->persist($user);

        $postVisible = new Post();
        $postVisible->setContent('Visible ' . $unique);
        $postVisible->setAuthor($user);
        $postVisible->setShowInFeed(true);
        $this->entityManager->persist($postVisible);

        $postHidden = new Post();
        $postHidden->setContent('Hidden ' . $unique);
        $postHidden->setAuthor($user);
        $postHidden->setShowInFeed(false);
        $this->entityManager->persist($postHidden);

        $this->entityManager->flush();

        /** @var PostRepository $repo */
        $repo = $this->entityManager->getRepository(Post::class);

        // Test standard findBy
        $visiblePosts = $repo->findBy(['showInFeed' => true, 'content' => 'Visible ' . $unique]);
        $this->assertCount(1, $visiblePosts);

        $hiddenPosts = $repo->findBy(['showInFeed' => false, 'content' => 'Hidden ' . $unique]);
        $this->assertCount(1, $hiddenPosts);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
