<?php

/**
 * -------------------------------------------------------------
 * Developed by Boban Milanovic BSc <boban.milanovic@gmail.com>
 *
 * Project: PawSocial Social Network
 * Description: A social network platform designed for pets, animal lovers,
 * animal shelters, and organizations to connect, share, and collaborate.
 *
 * This software is proprietary and confidential. Any use, reproduction, or
 * distribution without explicit written permission from the author is strictly prohibited.
 *
 * For licensing or collaboration inquiries, please contact:
 * Email: boban.milanovic@gmail.com
 * -------------------------------------------------------------
 *
 * Class: RegistrationFormType
 * Description: Defines the form structure for user registration.
 * Responsibilities:
 * - Builds the registration form.
 * - Includes fields for email, password, and terms agreement.
 * - Adds constraints/validation (e.g., password length).
 * -------------------------------------------------------------
 */



namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CountryType; // Add this
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;


class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('accountType', ChoiceType::class, [
                'choices' => [
                    'Private User' => 'private',
                    'Commercial / Organization' => 'commercial',
                ],
                'expanded' => true,
                'multiple' => false,
                'data' => 'private', // Default
            ])
            ->add('street', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['required' => false])
            ->add('houseNumber', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['required' => false])
            ->add('zipCode', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['required' => true])
            ->add('city', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['required' => true])
            ->add('country', \Symfony\Component\Form\Extension\Core\Type\CountryType::class, [
                'required' => true,
                'preferred_choices' => ['DE', 'AT', 'CH'],
                'data' => 'DE'
            ])
            ->add('email')
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue(
                        message: 'You should agree to our terms.',
                    ),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(
                        message: 'Please enter a password',
                    ),
                    new Length(
                        min: 6,
                        max: 4096,
                        minMessage: 'Your password should be at least {{ limit }} characters',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}