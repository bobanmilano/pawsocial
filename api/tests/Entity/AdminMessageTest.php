<?php

namespace App\Tests\Entity;

use App\Entity\AdminMessage;
use App\Entity\User;
use App\Entity\Post;
use PHPUnit\Framework\TestCase;

class AdminMessageTest extends TestCase
{
    public function testAdminMessageLogic(): void
    {
        $msg = new AdminMessage();
        $this->assertNotNull($msg->getCreatedAt());
        $this->assertFalse($msg->isRead());

        $sender = new User();
        $msg->setSender($sender);
        $this->assertSame($sender, $msg->getSender());

        $post = new Post();
        $msg->setRelatedPost($post);
        $this->assertSame($post, $msg->getRelatedPost());

        $msg->setMessage('Spam content!');
        $this->assertEquals('Spam content!', $msg->getMessage());

        $msg->setIsRead(true);
        $this->assertTrue($msg->isRead());
    }
}
