<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    public function testMyPackLoads(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail('profiletest_' . uniqid() . '@test.com');
        $user->setPassword('password');
        $user->setFirstName('Profile');
        $em->persist($user);
        $em->flush();

        $client->loginUser($user);
        $client->request('GET', '/my-pack/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Your Pack'); // Assuming h2 or similar
    }
}
