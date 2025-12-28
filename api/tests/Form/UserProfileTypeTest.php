<?php

namespace App\Tests\Form;

use App\Form\UserProfileType;
use App\Entity\User;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        // Strategy: Extend VichImageType and override constructor.
        $vichMock = new class extends VichImageType {
            public function __construct()
            {
            }
            public function configureOptions(OptionsResolver $resolver): void
            {
                parent::configureOptions($resolver);
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
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'organizationName' => 'Shelter X',
        ];

        $model = new User();
        $form = $this->factory->create(UserProfileType::class, $model);

        $expected = new User();
        $expected->setFirstName('Jane');
        $expected->setLastName('Doe');
        $expected->setOrganizationName('Shelter X');

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected->getFirstName(), $model->getFirstName());
        $this->assertEquals($expected->getLastName(), $model->getLastName());
    }
}
