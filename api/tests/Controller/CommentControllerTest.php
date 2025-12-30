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
    private function createPost(EntityManagerInterface $em, ?User $author = null): Post
    {
        if (!$author) {
            $author = new User();
            $author->setEmail('comment_author_' . uniqid() . '@example.com');
            $author->setPassword('password');
            $author->setFirstName('Author');
            $author->setCountry('DE');
            $em->persist($author);
        }

        $post = new Post();
        $post->setContent('Post to comment ' . uniqid());
        $post->setAuthor($author);
        $post->setShowInFeed(true);
        $em->persist($post);
        $em->flush();

        return $post;
    }

    private function createUser(EntityManagerInterface $em, ?string $email = null): User
    {
        $user = new User();
        $user->setEmail($email ?? ('commenter_' . uniqid() . '@example.com'));
        $user->setPassword('password');
        $user->setFirstName('Commenter');
        $user->setLastName('Test');
        $user->setCountry('DE');
        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function testAddComment(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $user = $this->createUser($em);
        $post = $this->createPost($em, $user);

        $client->loginUser($user);

        // Go to feed to find the form
        $crawler = $client->request('GET', '/feed');
        $this->assertResponseIsSuccessful();

        // Find the comment form for this post
        $form = $crawler->filter('form[action="/post/' . $post->getId() . '/comment"]')->form();

        $uniqueContent = 'Test comment ' . uniqid();
        $client->submit($form, [
            'content' => $uniqueContent
        ]);

        $this->assertResponseRedirects();

        // Verify database
        $comment = $em->getRepository(Comment::class)->findOneBy(['content' => $uniqueContent]);
        $this->assertNotNull($comment);
        $this->assertEquals($user->getId(), $comment->getAuthor()->getId());
        $this->assertEquals($post->getId(), $comment->getPost()->getId());
    }

    public function testDeleteCommentAccess(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $owner = $this->createUser($em, 'owner_' . uniqid() . '@test.com');
        $commenter = $this->createUser($em, 'commenter_' . uniqid() . '@test.com');
        $post = $this->createPost($em, $owner);

        // Create initial comment
        $comment = new Comment();
        $comment->setContent('Comment to delete ' . uniqid());
        $comment->setAuthor($commenter);
        $comment->setPost($post);
        $em->persist($comment);
        $em->flush();
        $commentId = $comment->getId();

        $em->clear(); // Ensure fresh load

        // Re-fetch objects for assertions and next steps
        $commenter = $em->getRepository(User::class)->find($commenter->getId());
        $owner = $em->getRepository(User::class)->find($owner->getId());
        $post = $em->getRepository(Post::class)->find($post->getId());

        // 2. Test SUCCESSFUL deletion (Author)
        $client->loginUser($commenter);
        $crawler = $client->request('GET', '/feed');

        // Debug: Check if comment is rendered
        $this->assertAnySelectorTextContains('body', $comment->getContent());

        // Find delete form for this comment
        $commentDeleteAction = '/comment/' . $commentId . '/delete';
        $formNode = $crawler->filter('form')->reduce(function ($node) use ($commentDeleteAction) {
            return str_contains($node->attr('action') ?? '', $commentDeleteAction);
        });

        $this->assertGreaterThan(0, $formNode->count(), 'Delete button should be visible for author. Expected: ' . $commentDeleteAction);

        $client->submit($formNode->form());
        $this->assertResponseRedirects();

        $em->clear();
        $this->assertNull($em->getRepository(Comment::class)->find($commentId));

        // Re-fetch for next part
        $commenter = $em->getRepository(User::class)->find($commenter->getId());
        $owner = $em->getRepository(User::class)->find($owner->getId());
        $post = $em->getRepository(Post::class)->find($post->getId());

        // 3. Test Owner can delete (Moderation)
        $newComment = new Comment();
        $newComment->setContent('Owner delete ' . uniqid());
        $newComment->setAuthor($commenter);
        $newComment->setPost($post);
        $em->persist($newComment);
        $em->flush();
        $newCommentId = $newComment->getId();
        $em->clear();

        $client->loginUser($owner);
        $crawler = $client->request('GET', '/feed');

        $newCommentDeleteAction = '/comment/' . $newCommentId . '/delete';
        $formNode = $crawler->filter('form')->reduce(function ($node) use ($newCommentDeleteAction) {
            return str_contains($node->attr('action') ?? '', $newCommentDeleteAction);
        });
        $this->assertGreaterThan(0, $formNode->count(), 'Delete button should be visible for post owner. Expected: ' . $newCommentDeleteAction);

        $client->submit($formNode->form());
        $this->assertResponseRedirects();

        $em->clear();
        $this->assertNull($em->getRepository(Comment::class)->find($newCommentId));
    }
}
