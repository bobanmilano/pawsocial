<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountSwitchTest extends WebTestCase
{
    private function createOwnerAndPet(EntityManagerInterface $em): array
    {
        $owner = new User();
        $owner->setEmail('owner_' . uniqid() . '@example.com');
        $owner->setPassword('$2y$13$...'); // dummy
        $owner->setFirstName('Owner');
        $em->persist($owner);

        $pet = new User();
        $pet->setEmail('pet_' . uniqid() . '@pawsocial.internal');
        $pet->setPassword('$2y$13$...');
        $pet->setFirstName('Buddy');
        $pet->setRoles(['ROLE_PET']);
        $pet->setAccountType('pet');
        $pet->setManagedBy($owner);
        $em->persist($pet);

        $em->flush();

        return [$owner, $pet];
    }

    public function testSwitchToPetSuccess(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        [$owner, $pet] = $this->createOwnerAndPet($em);

        $client->loginUser($owner);

        // Perform switch
        $client->request('GET', '/auth/switch/' . $pet->getId());
        $this->assertResponseRedirects('/feed');
        $client->followRedirect();

        // Verify we are logged in as pet (can check 'user' in session or profile page content, but easiest is to check next request context)
        // Since we mocked loginUser locally in test client, 'loginUser' helper persists. But manual switch changes token storage.
        // WebTestCase client->loginUser() sets a persistent token for requests. 
        // Our controller completely replaces the token in storage. 
        // Syncing test client state with server state is tricky in functional tests.
        // Instead, we verify that the Session contains the impersonation key.

        $this->assertNotNull($client->getRequest()->getSession()->get('_impersonating_user_id'));
        $this->assertEquals($owner->getId(), $client->getRequest()->getSession()->get('_impersonating_user_id'));
    }

    public function testSwitchForbidden(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        [$owner, $pet] = $this->createOwnerAndPet($em);

        $stranger = new User();
        $stranger->setEmail('stranger_' . uniqid() . '@example.com');
        $stranger->setPassword('...');
        $em->persist($stranger);
        $em->flush();

        $client->loginUser($stranger);
        $client->request('GET', '/auth/switch/' . $pet->getId());

        $this->assertResponseStatusCodeSame(403);
    }
}
