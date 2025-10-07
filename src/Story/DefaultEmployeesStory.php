<?php

namespace App\Story;

use App\Factory\CompanyFactory;
use App\Factory\EmployeeFactory;
use Zenstruck\Foundry\Story;

final class DefaultEmployeesStory extends Story
{
    public function build(): void
    {
        CompanyFactory::createMany(50);
        EmployeeFactory::createMany(150, function () {
            return [
                "company" => CompanyFactory::random()
            ];
        });
    }
}
