<?php

namespace App\Tests\Entity;

use App\Entity\Animal;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class AnimalTest extends TestCase
{
    public function testAnimalSettersAndGetters(): void
    {
        $animal = new Animal();
        $user = new User();

        $animal->setName('Buddy');
        $this->assertEquals('Buddy', $animal->getName());

        $animal->setSpecies('Dog');
        $this->assertEquals('Dog', $animal->getSpecies());

        $animal->setBreed('Golden Retriever');
        $this->assertEquals('Golden Retriever', $animal->getBreed());

        $animal->setUserAccount($user);
        $this->assertSame($user, $animal->getUserAccount());

        $date = new \DateTime('2020-01-01');
        $animal->setBirthDate($date);
        $this->assertSame($date, $animal->getBirthDate());

        $animal->setGender('Male');
        $this->assertEquals('Male', $animal->getGender());
    }
}
