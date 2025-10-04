<?php

namespace App\Tests;

use App\Entity\Company;
use App\Factory\CompanyFactory;
use App\Factory\EmployeeFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CompanyTest extends WebTestCase
{
    use ResetDatabase, Factories;

    private string $apiUrl = '/api/company';
              
    public function testGetCollection(): void
    {
        $client = static::createClient();
        CompanyFactory::createMany(100);
        
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
    
    public function testJsonResponseDoesNotContainEscapedUnicode(): void
    {
        $client = static::createClient();
        CompanyFactory::createOne(['name'=> 'Grzęgorz Brzęczyszczykiewicz Acme Sp. z o.o.']);
        $client->request('GET', $this->apiUrl);
        $this->assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        $this->assertNotFalse($content, 'Response content should not be false');
        $this->assertDoesNotMatchRegularExpression('/\\\\u[0-9a-fA-F]{4}/', $content, 'Response contains escaped Unicode characters');
    }

    public function testCreateCompany(): void
    {
        $client = static::createClient();
       
        $data = new CompanyFactory()->generateRandomFeed();
        $client->request(
                method: 'POST', 
                uri: $this->apiUrl,
                server: ['CONTENT_TYPE' => 'application/json'], 
                content: json_encode($data)
        );

        $this->assertResponseIsSuccessful(message:"not succesfull response, from post message");
        $content = $client->getResponse()->getContent();
        $this->assertNotEmpty($content);
        
        $company = json_decode($content, associative: true);

        $this->assertEquals($data['name'], $company['name'], "name of company is wrong!");
        $this->assertEquals($data['street'], $company['street'], "street is wrong for company!");
        $this->assertEquals($data['taxReferenceNumber'], $company['taxReferenceNumber'],"tax is wrong for company");
        $this->assertEquals($data['zipcode'], $company['zipcode'], "zip codeis wrong for company");
        
        $this->assertGreaterThan(0, $company['id']);
        $this->assertNotNull($company['createdAt']);
        $this->assertNotNull($company['updatedAt']);     
    }
    
    public function testShow(): void 
    {
        $client = static::createClient();
        $company = CompanyFactory::createOne();
        
        $client->request(
                method: 'GET', 
                uri: $this->apiUrl."/{$company->getId()}",
                server: ['CONTENT_TYPE' => 'application/json'], 
                content: json_encode($company)
        );
        
        $this->assertResponseIsSuccessful();
        $json = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($company->getId(), $json['id']);
    }

    public function testValidation(): void
    {
        $client = static::createClient();
        $company = new CompanyFactory()->generateRandomFeed();
        $company['taxReferenceNumber'] = 'Test 123456';
        
        $client->request(
                method: 'POST', 
                uri: $this->apiUrl,
                server: ['CONTENT_TYPE' => 'application/json'], 
                content: json_encode($company)
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testPutUpdate(): void 
    {
        $client = static::createClient();
        $oldName = "Old Company S.A.";
        $newName = "New Company S.A";
        $company = CompanyFactory::createOne(['name' => $oldName]);
        
        $array = [
            'name' => $newName
        ];
        
        $client->request(
                method: 'PUT', 
                uri: $this->apiUrl."/{$company->getId()}",
                server: ['CONTENT_TYPE' => 'application/json'], 
                content: json_encode($array)
        );
        
        $this->assertResponseIsSuccessful();
        $this->assertEquals($newName, json_decode($client->getResponse()->getContent(),true)["name"]);
    }
    
    public function testPutUpdateIfNoCompany(): void 
    {
        $client = static::createClient();
        $newName = "New Company S.A";
        
        $array = [
            'name' => $newName
        ];

        $id = -10;

        $client->request(
                method: 'PUT', 
                uri: $this->apiUrl."/$id",
                server: ['CONTENT_TYPE' => 'application/json'], 
                content: json_encode($array)
        );
        
        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteCompany(): void
    {
        $client = static::createClient();
        $company = CompanyFactory::createOne();
        
        $client->request(
                method: 'DELETE', 
                uri: $this->apiUrl."/{$company->getId()}",
                server: ['CONTENT_TYPE' => 'application/json'], 
        );
        
        $this->assertResponseStatusCodeSame(204);
        $result = static::getContainer()->get('doctrine')->getRepository(Company::class)->findById($company->getId());
        $this->assertEmpty($result);
    }
    
    public function testDeleteWithNotExistingId(): void
    {
        $client = static::createClient();
        $company = CompanyFactory::createOne();
        
        $id = -10;
        
        $client->request(
                method: 'DELETE', 
                uri: $this->apiUrl."/$id",
                server: ['CONTENT_TYPE' => 'application/json'], 
        );
        
        $this->assertResponseStatusCodeSame(404);
        
        //sanity check
        $result = static::getContainer()->get('doctrine')->getRepository(Company::class)->findById($company->getId());
        $this->assertNotEmpty($result);
    }

    public function testCreateWithEmployeers(): void
    {
        $client = static::createClient();
       
        $data = new CompanyFactory()->generateRandomFeed();
        
        $employeeFactory = new EmployeeFactory();
        $employeers = [];
        
        $amountOfEmployeers = 10;
        for($i = 0; $i < $amountOfEmployeers; $i++) {
            $employeers[] = $employeeFactory->generateRandomFeed();
        }
        
        $data['employeers'] = $employeers;
        
        $client->request(
                method: 'POST', 
                uri: $this->apiUrl,
                server: ['CONTENT_TYPE' => 'application/json'], 
                content: json_encode($data)
        );

        $this->assertResponseIsSuccessful(message:"not succesfull response, from post message");
        $content = $client->getResponse()->getContent();
        $this->assertNotEmpty($content);
        
        $this->assertCount($amountOfEmployeers, json_decode($content, associative: true)['employeers']);
    }
    
    public function testCreateCompanyWithEmployeersWithoutName(): void 
    {
        $client = static::createClient();
       
        $data = new CompanyFactory()->generateRandomFeed();
        
        $employeeFactory = new EmployeeFactory();
        $employeers = [];
        
        $amountOfEmployeers = 10;
        for($i = 0; $i < $amountOfEmployeers; $i++) {
            $feed = $employeeFactory->generateRandomFeed();
            $feed['name'] = '';
            $employeers[] = $feed;
        }
        
        $data['employeers'] = $employeers;
        
        $client->request(
                method: 'POST', 
                uri: $this->apiUrl,
                server: ['CONTENT_TYPE' => 'application/json'], 
                content: json_encode($data)
        );

        $this->assertResponseStatusCodeSame(400);
    }
    
    public function testUpdateCompanyWihtEmploeeyrs(): void 
    {
        $client = static::createClient();
        $company = CompanyFactory::createOne();
        $newCompany = CompanyFactory::createOne();

        $amountOfEmployeers = 10;
        $employeers = EmployeeFactory::createMany($amountOfEmployeers ,['company' => $company]);
        
        $mapedData = array_map(fn ($employee) => [
            'id' => $employee->getId(),
            'name' => $employee->getName(),
            'surname' => $employee->getSurname(),
            'email' => $employee->getEmail(),
        ],$employeers);
        
        $array = [
            'employeers' => $mapedData
        ];
        
        $client->request(
                method: 'PUT', 
                uri: $this->apiUrl."/{$newCompany->getId()}",
                server: ['CONTENT_TYPE' => 'application/json'], 
                content: json_encode($array)
        );
        
     
        $this->assertResponseIsSuccessful();
        $repository = static::getContainer()->get('doctrine')->getRepository(Company::class);
        $oldEmployeers = $repository->find($company->getId())->getEmployeers();
        static::assertCount(0,$oldEmployeers);
        $newEmployeers = $repository->find($newCompany->getId())->getEmployeers();
        static::assertCount($amountOfEmployeers, $newEmployeers);
    }
    
    public function testUpdateCompanyWihtEmploeeyrsWithoutName(): void 
    {
        $client = static::createClient();
        $company = CompanyFactory::createOne();
        $newCompany = CompanyFactory::createOne();

        $amountOfEmployeers = 10;
        $employeers = EmployeeFactory::createMany($amountOfEmployeers ,['company' => $company]);
        
        $mapedData = array_map(fn ($employee) => [
            'id' => $employee->getId(),
            'name' => '',
            'surname' => $employee->getSurname(),
            'email' => $employee->getEmail(),
        ],$employeers);
        
        $array = [
            'employeers' => $mapedData
        ];
        
        $client->request(
                method: 'PUT', 
                uri: $this->apiUrl."/{$newCompany->getId()}",
                server: ['CONTENT_TYPE' => 'application/json'], 
                content: json_encode($array)
        );
        
     
        $this->assertResponseStatusCodeSame(400);
    }
}
