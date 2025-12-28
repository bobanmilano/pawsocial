<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Post;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserInitialState(): void
    {
        $user = new User();
        $this->assertNull($user->getId());
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertEquals('private', $user->getAccountType());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $user->getPosts());
    }

    public function testSettersAndGetters(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $user->getUserIdentifier());
        $this->assertEquals('test@example.com', $user->getEmail());

        $user->setFirstName('John');
        $user->setLastName('Doe');
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals('John Doe', $user->getFirstName() . ' ' . $user->getLastName());

        $user->setOrganizationName('Acme Inc.');
        $this->assertEquals('Acme Inc.', $user->getOrganizationName());

        $user->setAccountType('commercial');
        $this->assertEquals('commercial', $user->getAccountType());
    }

    public function testRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN_USER']);
        $this->assertContains('ROLE_USER', $user->getRoles()); // Should always be present
        $this->assertContains('ROLE_ADMIN_USER', $user->getRoles());
    }
}
