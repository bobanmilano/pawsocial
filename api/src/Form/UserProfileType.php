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
 * Class: UserProfileType
 * Description: Defines the form structure for user profile editing.
 * Responsibilities:
 * - Builds the form for editing user profile.
 * - Includes fields for name, organization, and profile picture.
 * -------------------------------------------------------------
 */

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;


class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['required' => false])
            ->add('lastName', TextType::class, ['required' => false])
            ->add('street', TextType::class, ['required' => false])
            ->add('houseNumber', TextType::class, ['required' => false])
            ->add('zipCode', TextType::class, ['required' => true])
            ->add('city', TextType::class, ['required' => true])
            ->add('country', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'Germany' => 'DE',
                    'Austria' => 'AT',
                    'Switzerland' => 'CH',
                    'France' => 'FR',
                    'Italy' => 'IT',
                    'Spain' => 'ES',
                    'Netherlands' => 'NL',
                    'Poland' => 'PL',
                    'Portugal' => 'PT',
                    'Sweden' => 'SE',
                    'Denmark' => 'DK',
                    'Finland' => 'FI',
                    'Greece' => 'GR',
                    'Czech Republic' => 'CZ',
                    'Hungary' => 'HU',
                    'Romania' => 'RO',
                    'Bulgaria' => 'BG',
                    'Croatia' => 'HR',
                    'Slovakia' => 'SK',
                    'Slovenia' => 'SI',
                    'Estonia' => 'EE',
                    'Latvia' => 'LV',
                    'Lithuania' => 'LT',
                    'Ireland' => 'IE',
                    'Malta' => 'MT',
                    'Belgium' => 'BE',
                    'Cyprus' => 'CY',
                    'Luxembourg' => 'LU',
                    'Liechtenstein' => 'LI',
                    'Serbia' => 'RS',
                    'Norway' => 'NO',
                    'Russia' => 'RU',
                    'United Kingdom' => 'GB',
                    'Ukraine' => 'UA',
                    'Turkey' => 'TR',
                ],
                'preferred_choices' => ['DE', 'AT', 'CH', 'LI'],
                'attr' => [
                    'class' => 'form-select',
                    'data-controller' => 'form-select'
                ],
            ])
            ->add('organizationName', TextType::class, [
                'required' => false,
                'label' => 'Organization Name (Optional)',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => false,
                'label' => 'Profile Picture',
                'delete_label' => 'Delete current image?', // Explicitly set label
                'asset_helper' => true,
            ])
            ->add('coverImageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => false,
                'label' => 'Cover Image',
                'delete_label' => 'Delete current cover?',
                'asset_helper' => true,
            ])
            ->add('locale', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => 'Preferred Language',
                'choices' => [
                    'Deutsch' => 'de',
                    'English' => 'en',
                    'Français' => 'fr',
                    'Italiano' => 'it',
                    'Español' => 'es',
                    'Nederlands' => 'nl',
                    'Polski' => 'pl',
                    'Português' => 'pt',
                    'Svenska' => 'sv',
                    'Dansk' => 'da',
                    'Suomi' => 'fi',
                    'Ελληνικά' => 'el',
                    'Čeština' => 'cs',
                    'Magyar' => 'hu',
                    'Română' => 'ro',
                    'Български' => 'bg',
                    'Hrvatski' => 'hr',
                    'Slovenčina' => 'sk',
                    'Slovenščina' => 'sl',
                    'Eesti' => 'et',
                    'Latviešu' => 'lv',
                    'Lietuvių' => 'lt',
                    'Gaeilge' => 'ga',
                    'Malti' => 'mt',
                    'Українська' => 'uk',
                    'Liechtenstein' => 'li',
                    'Српски' => 'sr',
                    'Norsk' => 'no',
                    'Русский' => 'ru',
                    'Türkçe' => 'tr',
                ],
                'attr' => [
                    'class' => 'form-select',
                    'data-controller' => 'form-select'
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