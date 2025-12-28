<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    public function testAdminAccessDeniedForRegularUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        // Create Regular User
        $user = new User();
        $user->setEmail('regular_' . uniqid() . '@test.com');
        $user->setPassword('password');
        $user->setFirstName('Regular');
        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        $client->loginUser($user);
        $client->request('GET', '/admin/users');

        $this->assertResponseStatusCodeSame(403);
    }
}
