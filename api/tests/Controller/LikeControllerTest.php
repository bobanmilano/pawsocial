<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LikeControllerTest extends WebTestCase
{
    private function createPost(EntityManagerInterface $em): Post
    {
        $user = new User();
        $user->setEmail('liker_author_' . uniqid() . '@example.com');
        $user->setPassword('password');
        $user->setFirstName('Author');
        $em->persist($user);

        $post = new Post();
        $post->setContent('Post to like ' . uniqid());
        $post->setAuthor($user);
        $post->setShowInFeed(true);
        $em->persist($post);
        $em->flush();

        return $post;
    }

    private function createUser(EntityManagerInterface $em): User
    {
        $user = new User();
        $user->setEmail('liker_' . uniqid() . '@example.com');
        $user->setPassword('password');
        $user->setFirstName('Liker');
        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function testLikeToggle(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $likeRepo = $container->get(PostLikeRepository::class);

        $post = $this->createPost($em);
        $user = $this->createUser($em);

        $client->loginUser($user);

        // First Like
        $client->request('POST', '/post/' . $post->getId() . '/like');
        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($response['isLiked']);
        $this->assertEquals(1, $response['count']);

        // Verify DB
        $like = $likeRepo->findOneBy(['post' => $post, 'user' => $user]);
        $this->assertNotNull($like);

        // Toggle (Unlike)
        $client->request('POST', '/post/' . $post->getId() . '/like');
        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($response['isLiked']);
        $this->assertEquals(0, $response['count']);

        // Verify DB null
        $em->clear(); // clear cache
        $like = $likeRepo->findOneBy(['post' => $post, 'user' => $user]);
        $this->assertNull($like);
    }
}
