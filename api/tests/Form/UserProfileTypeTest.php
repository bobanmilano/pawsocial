<?php

namespace App\Tests\Form;

use App\Form\UserProfileType;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserProfileTypeTest extends KernelTestCase
{
    public function testSubmitValidData(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $factory = $container->get('form.factory');

        $formData = [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'organizationName' => 'Shelter X',
            'zipCode' => '12345',
            'city' => 'TestCity',
            'country' => 'DE',
            'locale' => 'en',
        ];

        $model = new User();
        $form = $factory->create(UserProfileType::class, $model);

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
