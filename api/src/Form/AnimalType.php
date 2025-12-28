<?php

/**
 * -------------------------------------------------------------
 * Developed by Boban Milanovic BSc <boban.milanovic@gmail.com>
 * 
 * Project: PawSocial Social Network
 * Description: A social network platform designed for pets, animal lovers, 
 *              animal shelters, and organizations to connect, share, and collaborate.
 * 
 * This software is proprietary and confidential. Any use, reproduction, or 
 * distribution without explicit written permission from the author is strictly prohibited.
 * 
 * For licensing or collaboration inquiries, please contact:
 * Email: boban.milanovic@gmail.com
 * -------------------------------------------------------------
 * 
 * Class: AnimalType
 * Description: Defines the form structure for Animal entity.
 * Responsibilities:
 * - Builds the form for adding/editing a pet.
 * - Configures form fields for animal details (name, species, breed, etc.).
 * - Handles file upload field configuration.
 * -------------------------------------------------------------
 */

namespace App\Form;

use App\Entity\Animal;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;


class AnimalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => ['placeholder' => 'e.g. Bella']
            ])
            ->add('species', ChoiceType::class, [
                'label' => 'Species',
                'choices' => [
                    'Dog' => 'dog',
                    'Cat' => 'cat',
                    'Bird' => 'bird',
                    'Small Animal' => 'small_animal',
                    'Horse' => 'horse',
                    'Other' => 'other',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('breed', TextType::class, [
                'label' => 'Breed',
                'required' => false,
                'attr' => ['placeholder' => 'e.g. Labrador Retriever']
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Gender',
                'choices' => [
                    'Male' => 'male',
                    'Female' => 'female',
                    'Unknown' => 'unknown',
                ],
                'required' => false,
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Birth Date (Approx.)',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('isAdoptable', CheckboxType::class, [
                'label' => 'Is this pet up for adoption?',
                'required' => false,
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => true,
                'label' => 'Pet Photo',
                'asset_helper' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Animal::class,
        ]);
    }
}
