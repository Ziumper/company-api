<?php

namespace App\Tests;

use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class EmployeeTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    private string $apiUrl = "/api/employees";

 
    
}
