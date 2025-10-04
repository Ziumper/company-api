<?php

namespace App\Tests;

use App\Factory\CompanyFactory;
use App\Factory\EmployeeFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class EmployeeTest extends WebTestCase
{
    use ResetDatabase, Factories;

    private string $apiUrl = "/api/employee";

     public function testGetCollection(): void
    {
        $client = static::createClient();
        CompanyFactory::createMany(100);
        CompanyFactory::createMany(50);
        
        EmployeeFactory::createMany(100, function() {
            return [ "company" => CompanyFactory::random()];
        });
        
        $client->request('GET', $this->apiUrl);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('limit', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertNotEmpty($data['data']);
        $this->assertCount($data['limit'], $data['data']);
    }

}
