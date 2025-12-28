<?php

namespace App\Tests\Repository;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private ?\Doctrine\ORM\EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testFindOneByEmail(): void
    {
        $unique = uniqid();
        $email = 'repo_user_' . $unique . '@example.com';

        $user = new User();
        $user->setEmail($email);
        $user->setPassword('password');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $repo = $this->entityManager->getRepository(User::class);
        $foundUser = $repo->findOneBy(['email' => $email]);

        $this->assertNotNull($foundUser);
        $this->assertEquals($email, $foundUser->getEmail());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
