<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\AdminMessageRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReportControllerTest extends WebTestCase
{
    private function createPost(string $title = 'Test Post'): Post
    {
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail('author_' . uniqid() . '@example.com');
        $user->setPassword('password');
        $user->setFirstName('Author');
        $em->persist($user);

        $post = new Post();
        $post->setContent($title);
        $post->setAuthor($user);
        $post->setShowInFeed(true);
        $em->persist($post);
        $em->flush();

        return $post;
    }

    private function createReporter(): User
    {
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail('reporter_' . uniqid() . '@example.com');
        $user->setPassword('password');
        $user->setFirstName('Reporter');
        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function testReportPostRequiresLogin(): void
    {
        $client = static::createClient();
        // create a post to try to report
        $post = $this->createPost();

        $client->request('POST', '/post/' . $post->getId() . '/report');
        $this->assertResponseRedirects('/login');
    }

    public function testReportPostSuccess(): void
    {
        $client = static::createClient();
        $post = $this->createPost();
        $reporter = $this->createReporter();

        $client->loginUser($reporter);

        $client->request('POST', '/post/' . $post->getId() . '/report', [
            'reason' => 'Inappropriate content'
        ]);

        $this->assertResponseRedirects('/feed');
        $client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        // Verify message in DB
        $container = static::getContainer();
        $msgRepo = $container->get(AdminMessageRepository::class);
        $msg = $msgRepo->findOneBy([], ['id' => 'DESC']);

        $this->assertNotNull($msg);
        $this->assertStringContainsString('Inappropriate content', $msg->getMessage());
        $this->assertSame($reporter->getId(), $msg->getSender()->getId());
        $this->assertSame($post->getId(), $msg->getRelatedPost()->getId());
    }

    public function testReportPostValidationError(): void
    {
        $client = static::createClient();
        $post = $this->createPost();
        $reporter = $this->createReporter();

        $client->loginUser($reporter);

        // Send empty reason
        $client->request('POST', '/post/' . $post->getId() . '/report', [
            'reason' => '   '
        ]);

        // Should NOT redirect to feed with success, but show error.
        // Controller redirects or renders? 
        // Logic: if(trim($reason)) { ... redirect } else { addFlash('danger') ... }
        // Then returns render(...)

        $this->assertResponseIsSuccessful(); // stays on page
        $this->assertSelectorExists('.alert-danger');
        $this->assertSelectorTextContains('.alert-danger', 'Please provide a reason');
    }
}
