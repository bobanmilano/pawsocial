<?php

namespace App\Tests\Form;

use App\Form\PostType;
use App\Entity\Post;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        // Strategy: Extend VichImageType and override constructor to do nothing.
        // This ensures the object IS valid and has the correct type inheritance.
        $vichMock = new class extends VichImageType {
            public function __construct()
            {
            }

            // Override configureOptions to avoid needing parent's logic if it uses injected services
            public function configureOptions(OptionsResolver $resolver): void
            {
                parent::configureOptions($resolver);
                // We might need to handle specific options if PostType sets them?
                // PostType sets: required, allow_delete, download_uri, ...
                // VichImageType's parent (AbstractType) handles most. 
                // But VichImageType might define defaults dependent on services.
                // Let's hope it doesn't crash here.
            }

            public function buildForm(FormBuilderInterface $builder, array $options): void
            {
            }
            public function buildView(FormView $view, FormInterface $form, array $options): void
            {
            }
        };

        return [
            new ValidatorExtension(Validation::createValidator()),
            new PreloadedExtension([
                VichImageType::class => $vichMock,
            ], []),
        ];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'content' => 'New Post Content',
            'videoUrl' => 'https://youtube.com/watch?v=123',
            'showInFeed' => true,
        ];

        $model = new Post();
        $form = $this->factory->create(PostType::class, $model);

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
