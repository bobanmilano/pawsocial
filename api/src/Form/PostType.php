<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\ToValidate;
use Symfony\Component\Validator\Constraints\Length;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'What is your pet up to?',
                'attr' => ['rows' => 3, 'placeholder' => 'Share a moment...'],
                'constraints' => [
                    new Length(['max' => 2000]),
                ],
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => true,
                'label' => 'Photo (max 150KB auto-compressed)',
                'asset_helper' => true,
            ])
            ->add('videoUrl', UrlType::class, [
                'required' => false,
                'label' => 'YouTube/Vimeo Link (Optional)',
                'attr' => ['placeholder' => 'https://youtube.com/...'],
            ])
            ->add('showInFeed', CheckboxType::class, [
                'label' => 'Show in Public Feed?',
                'required' => false,
                'help' => 'If unchecked, this post will only be visible on your profile.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\Callback([$this, 'validateMedia']),
            ],
        ]);
    }

    public function validateMedia(Post $post, \Symfony\Component\Validator\Context\ExecutionContextInterface $context): void
    {
        if ($post->getImageFile() && $post->getVideoUrl()) {
            $context->buildViolation('Please choose either an image OR a video link, not both.')
                ->atPath('imageFile')
                ->addViolation();
        }
    }
}
