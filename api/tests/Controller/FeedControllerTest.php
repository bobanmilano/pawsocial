<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeedControllerTest extends WebTestCase
{
    public function testFeedPageRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/feed');
        $this->assertResponseRedirects('/login');
    }

    public function testFeedPageLoadsForUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // Retrieve the test user created during fixtures or registration tests
        // Using 'boban.milanovic@gmail.com' as he is admin/active
        $testUser = $userRepository->findOneBy(['email' => 'boban.milanovic@gmail.com']);

        // If no user exists (fresh DB), creates one
        if (!$testUser) {
            $testUser = new User();
            $testUser->setEmail('feedtest_' . uniqid() . '@example.com');
            $testUser->setPassword('$2y$13$bwvE/..'); // Dummy hash
            $testUser->setFirstName('Test');
            $testUser->setLastName('User');

            $container = static::getContainer();
            $em = $container->get('doctrine')->getManager();
            $em->persist($testUser);
            $em->flush();
        }

        $client->loginUser($testUser);

        $client->request('GET', '/feed');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form'); // Post creation form
    }
}
