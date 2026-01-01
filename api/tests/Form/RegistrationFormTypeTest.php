<?php

namespace App\Tests\Form;

use App\Form\RegistrationFormType;
use App\Entity\User;
use Symfony\Component\Form\Test\TypeTestCase;

use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

class RegistrationFormTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => 'test@example.com',
            'plainPassword' => 'password123',
            'agreeTerms' => true,
            'accountType' => 'private',
            'zipCode' => '12345',
            'city' => 'TestCity',
            'country' => 'DE',
        ];

        $model = new User();
        // $model will not be populated with plainPassword as it's not mapped? 
        // Wait, plainPassword IS mapped but with mapped=false usually in RegistrationForm.
        // Let's check the form type. If mapped=false, $model won't get it.
        // But email and accountType should be there.

        $form = $this->factory->create(RegistrationFormType::class, $model);

        $expected = new User();
        $expected->setEmail('test@example.com');
        $expected->setAccountType('private');
        $expected->setCountry('DE');
        // agreeTerms also mapped=false usually.

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected->getEmail(), $model->getEmail());
        $this->assertEquals($expected->getAccountType(), $model->getAccountType());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
