<?php

namespace App\DataFixtures;

use App\Story\DefaultEmployeesStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        DefaultEmployeesStory::load();
    }
}
