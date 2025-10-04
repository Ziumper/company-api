<?php

namespace App\Tests;

use App\Entity\Employee;
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
        static::assertResponseIsSuccessful();
        static::assertResponseHeaderSame('content-type', 'application/json');
        static::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        
        static::assertArrayHasKey('page', $data);
        static::assertArrayHasKey('limit', $data);
        static::assertArrayHasKey('total', $data);
        static::assertNotEmpty($data['data']);
        static::assertCount($data['limit'], $data['data']);
    }
    
      public function testCreateEmployee(): void
    {
        $client = static::createClient();
        $company = CompanyFactory::createOne();
        
        $data = new EmployeeFactory()->generateRandomFeed();
        $data['company'] = [
            'id' => $company->getId(),
        ];
        
        
        $client->request(
                method: 'POST', 
                uri: $this->apiUrl,
                server: ['CONTENT_TYPE' => 'application/json'], 
                content: json_encode($data)
        );

        static::assertResponseIsSuccessful(message:"not succesfull response, from post message");
        $content = $client->getResponse()->getContent();
        static::assertNotEmpty($content);
        
        $employee = json_decode($content, associative: true);

        static::assertGreaterThan(0, $employee['id']);
        static::assertNotNull($employee['createdAt']);
        static::assertNotNull($employee['updatedAt']);     
    }
    
    
      public function testShow(): void 
    {
        $client = static::createClient();
        $company = CompanyFactory::createOne();
        $employee = EmployeeFactory::createOne(['company'=> $company]);
        
        $client->request(
                method: 'GET', 
                uri: $this->apiUrl."/{$employee->getId()}",
        );
        
        static::assertResponseIsSuccessful();
        $json = json_decode($client->getResponse()->getContent(), true);
        static::assertEquals($employee->getId(), $json['id']);
    }

    public function testValidationCreateNoNameEmployeeFail(): void
    {
        $client = static::createClient();
        $company = CompanyFactory::createOne();
        
        $data = new EmployeeFactory()->generateRandomFeed();
        $data['name'] = '';
        $data['company'] = [
            'id' => $company->getId(),
        ];
        
        $client->request(
                method: 'POST', 
                uri: $this->apiUrl,
                server: ['CONTENT_TYPE' => 'application/json'], 
                content: json_encode($data)
        );

        static::assertResponseStatusCodeSame(400);
    }

    public function testPutUpdate(): void 
    {
        $client = static::createClient();
        $company = CompanyFactory::createOne();
        $employee = EmployeeFactory::createOne([
            'company' => $company
        ]);
                
        $newName = 'Janusz Biznesu';
        
        $array = [
            'name' => $newName
        ];
        
        $client->request(
                method: 'PUT', 
                uri: $this->apiUrl."/{$employee->getId()}",
                server: ['CONTENT_TYPE' => 'application/json'], 
                content: json_encode($array)
        );
        
        static::assertResponseIsSuccessful();
        static::assertEquals($newName, json_decode($client->getResponse()->getContent(),true)["name"]);
    }
    
    public function testPutUpdateFailWithNoName(): void 
    {
        $client = static::createClient();
        $company = CompanyFactory::createOne();
        $employee = EmployeeFactory::createOne([
            'company' => $company
        ]);
                
        $newName = '';
        
        $array = [
            'name' => $newName
        ];
        
        $client->request(
                method: 'PUT', 
                uri: $this->apiUrl."/{$employee->getId()}",
                server: ['CONTENT_TYPE' => 'application/json'], 
                content: json_encode($array)
        );
        
        static::assertResponseStatusCodeSame(400);
        
        //sanity check
        $repository = static::getContainer()->get('doctrine')->getRepository(Employee::class);
        $fetchedEmployee = $repository->find($employee->getId());
        static::assertEquals($employee->getName(),$fetchedEmployee->getName());
    }
    
    public function testDeleteEmployee() {
        $client = static::createClient();
        $company = CompanyFactory::createOne();
        $employee = EmployeeFactory::createOne([
            'company' => $company
        ]);
        
        $client->request(
                method: 'DELETE', 
                uri: $this->apiUrl."/{$employee->getId()}",
                server: ['CONTENT_TYPE' => 'application/json'], 
        );
        
        $this->assertResponseStatusCodeSame(204);
    }

}
