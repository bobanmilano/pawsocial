<?php

namespace App\Tests\Entity;

use App\Entity\Post;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function testPostInitialState(): void
    {
        $post = new Post();
        $this->assertNotNull($post->getCreatedAt());
        // Defaults
        $this->assertTrue($post->isShowInFeed()); 
    }

    public function testSettersAndGetters(): void
    {
        $post = new Post();
        $user = new User(); // Mock or real object since it's a simple POJO relation test here
        
        $post->setAuthor($user);
        $this->assertSame($user, $post->getAuthor());

        $post->setContent('Hello World');
        $this->assertEquals('Hello World', $post->getContent());

        $post->setVideoUrl('https://youtube.com/watch?v=12345678901');
        $this->assertEquals('12345678901', $post->getYouTubeId());
        
        // Shorts
        $post->setVideoUrl('https://youtube.com/shorts/abcdefghijk');
        $this->assertEquals('abcdefghijk', $post->getYouTubeId());

        $post->setShowInFeed(false);
        $this->assertFalse($post->isShowInFeed());
    }
}
