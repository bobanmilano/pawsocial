<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase
{
    public function testHomePageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        // Main page redirects to /login for anon users or /feed for logged in
        // Default might be MainController rendering landing page?
        // Let's check MainController behavior.
        // It says: return $this->render('main/index.html.twig'); but redirects if logged in.

        $this->assertSelectorTextContains('h1', 'Where the Pack Connects');
    }

    public function testLoginPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Welcome Back!');
    }
}
