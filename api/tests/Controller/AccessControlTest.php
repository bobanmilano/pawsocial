<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccessControlTest extends WebTestCase
{
    public function testAdminDashboardAccessDeniedForAnon(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/users');

        // Should redirect to login
        $this->assertResponseRedirects('/login');
    }

    public function testInternalPagesRedirectAnon(): void
    {
        $client = static::createClient();
        $client->request('GET', '/feed');
        $this->assertResponseRedirects('/login');

        $client->request('GET', '/my-pack/');
        $this->assertResponseRedirects('/login');
    }

    // Note: To test actual Admin access we need to log in as admin.
    // We can simulate this if we have a way to persist a user.
    // For now, testing the protection (failure case) is the critical part for security.
}
