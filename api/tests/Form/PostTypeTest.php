<?php

namespace App\Tests\Form;

use App\Form\PostType;
use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PostTypeTest extends KernelTestCase
{
    public function testSubmitValidData(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $factory = $container->get('form.factory');

        $formData = [
            'content' => 'New Post Content',
            'videoUrl' => 'https://youtube.com/watch?v=123',
            'showInFeed' => true,
        ];

        $model = new Post();
        $form = $factory->create(PostType::class, $model);

        $expected = new Post();
        $expected->setContent('New Post Content');
        $expected->setVideoUrl('https://youtube.com/watch?v=123');
        $expected->setShowInFeed(true);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected->getContent(), $model->getContent());
        $this->assertEquals($expected->getVideoUrl(), $model->getVideoUrl());
        $this->assertTrue($model->isShowInFeed());
    }
}
