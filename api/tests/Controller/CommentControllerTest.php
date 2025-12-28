<?php

namespace App\Tests\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentControllerTest extends WebTestCase
{
    private function createPost(EntityManagerInterface $em): Post
    {
        $user = new User();
        $user->setEmail('comment_author_' . uniqid() . '@example.com');
        $user->setPassword('password');
        $user->setFirstName('Author');
        $em->persist($user);

        $post = new Post();
        $post->setContent('Post to comment ' . uniqid());
        $post->setAuthor($user);
        $post->setShowInFeed(true);
        $em->persist($post);
        $em->flush();

        return $post;
    }

    private function createUser(EntityManagerInterface $em): User
    {
        $user = new User();
        $user->setEmail('commenter_' . uniqid() . '@example.com');
        $user->setPassword('password');
        $user->setFirstName('Commenter');
        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function testAddComment(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $post = $this->createPost($em);
        $user = $this->createUser($em);

        $client->loginUser($user);

        $uniqueContent = 'Test comment ' . uniqid();
        $client->request('POST', '/post/' . $post->getId() . '/comment', [
            'content' => $uniqueContent
        ]);

        $this->assertResponseRedirects('/feed');

        $commentRepo = $container->get(CommentRepository::class);
        $comment = $commentRepo->findOneBy(['content' => $uniqueContent]);

        $this->assertNotNull($comment);
        $this->assertSame($post->getId(), $comment->getPost()->getId());
        $this->assertSame($user->getId(), $comment->getAuthor()->getId());
    }

    public function testDeleteCommentAccess(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $post = $this->createPost($em);
        $owner = $post->getAuthor();
        $commenter = $this->createUser($em);
        $stranger = $this->createUser($em);

        // Create comment by commenter
        $comment = new Comment();
        $comment->setContent('Delete me');
        $comment->setAuthor($commenter);
        $comment->setPost($post);
        $em->persist($comment);
        $em->flush();
        $commentId = $comment->getId();

        // Stranger cannot delete
        $client->loginUser($stranger);
        $client->request('POST', '/comment/' . $commentId . '/delete');
        $this->assertResponseStatusCodeSame(403);

        // Author can delete
        $client->loginUser($commenter);
        $client->request('POST', '/comment/' . $commentId . '/delete');
        $this->assertResponseRedirects('/feed');

        // Reload entities because EM might have been cleared/closed or client rebooted kernel
        $post = $em->getRepository(Post::class)->find($post->getId());
        $commenter = $em->getRepository(User::class)->find($commenter->getId());

        // Post Owner can also delete (Need to create new comment)
        $comment2 = new Comment();
        $comment2->setContent('Owner delete');
        $comment2->setAuthor($commenter);
        $comment2->setPost($post);
        $em->persist($comment2);
        $em->flush();
        $comment2Id = $comment2->getId();

        $client->loginUser($owner);
        $client->request('POST', '/comment/' . $comment2Id . '/delete');
        $this->assertResponseRedirects('/feed');
    }
}
