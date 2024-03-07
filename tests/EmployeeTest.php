<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Employee;
use App\Dto\EmployeeDto;
use App\Factory\CompanyFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use App\Factory\EmployeeFactory;
use App\Entity\Company;

class EmployeeTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    private string $apiUrl = "/api/employees";

    public function testGetCollectionEmployee(): void
    {
        CompanyFactory::createMany(50);
        EmployeeFactory::createMany(100, function() {
            return [ "company" => CompanyFactory::random()];
        });
    
        $response = static::createClient()->request('GET', $this->apiUrl);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Employee',
            '@id' => $this->apiUrl,
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => $this->apiUrl.'?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => $this->apiUrl.'?page=1',
                'hydra:last' => $this->apiUrl.'?page=4',
                'hydra:next' => $this->apiUrl.'?page=2',
            ],
        ]);

        $this->assertCount(30, $response->toArray()['hydra:member']);

        $this->assertMatchesResourceCollectionJsonSchema(Employee::class);
        $this->assertResponseIsSuccessful();
    }

    public function testCreateEmployee(): void
    {
        $id = CompanyFactory::createOne()->getId();
        $iri = $this->findIriBy(Company::class, ['id' => $id]);
       
        $faker = EmployeeFactory::faker();
        $jsonRequestArray =  [
            'name' => $faker->firstName(),
            'surname' => $faker->lastName(),
            'email' => $faker->email(),
            'company' => $iri,
            'phoneNumber' => $faker->phoneNumber()
        ];

        /**
         * @var \Symfony\Contracts\HttpClient\ResponseInterface $response
         */
        $response = static::createClient()->request('POST', $this->apiUrl, 
        [
            'json' => $jsonRequestArray, 
            'headers'=> [
                'content-type' => 'application/ld+json'
            ]]);

        $content = $response->toArray();
        
        foreach ($jsonRequestArray as $key => $value) {
            $this->assertEquals($value,$content[$key]);
        }
        
        $this->assertResponseIsSuccessful(message:"not succesfull response, from post message");
        $this->assertMatchesResourceItemJsonSchema(Employee::class);
    }

    public function testWrongEmailEmployeeCreate(): void
    {
        $employee = new EmployeeDto();
        $id = CompanyFactory::createOne()->getId();
        $iri = $this->findIriBy(Company::class, ['id' => $id]);

        /**
         * @var \Faker\Generator $faker
         */
        $faker = EmployeeFactory::faker();
        $employee->name = $faker->firstName();
        $employee->email = "WrongMailFormat";
        $employee->phoneNumber = $faker->phoneNumber();
        $employee->surname = $faker->lastName();
        $employee->company = $iri;

        $array = json_decode(json_encode($employee), true);
        
        static::createClient()->request('POST', $this->apiUrl, 
        [
            'json' => $array,    
            'headers'=> [
                'content-type' => 'application/ld+json'
            ]
            ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testEmptyPhoneNumberCreate(): void
    {
        $id = CompanyFactory::createOne()->getId();
        $iri = $this->findIriBy(Company::class, ['id' => $id]);

        $employee = new EmployeeDto();

        /**
         * @var \Faker\Generator $faker
         */
        $faker = CompanyFactory::faker();
        $employee->name = $faker->firstName();
        $employee->email = $faker->email();
        $employee->phoneNumber = "";
        $employee->surname = $faker->lastName();
        $employee->company = $iri;

        $array = json_decode(json_encode($employee), true);
        
        static::createClient()->request('POST', $this->apiUrl, 
        [
            'json' => $array,    
            'headers'=> [
                'content-type' => 'application/ld+json'
            ]
        ]);

        $this->assertResponseIsSuccessful(message:"not succesfull response, from post message");
    }

    public function testPartialUpdateEmployee() {
        $oldName = "Franek";
        $newName = "Dolas";

        $company = CompanyFactory::createOne();
        EmployeeFactory::createOne(
            [ 
                'name' => $oldName,
                'company' => $company
            ]
        );

        $iri = $this->findIriBy(Employee::class, ['name' => $oldName]);
        static::createClient()->request('PATCH',$iri,[
            'json' => [
                'name' => $newName
            ],
            'headers' => [
                'content-type' => 'application/merge-patch+json'
            ]
            ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => $newName
        ]);
    }
   
    public function testPartialUpdateEmployeeFailWithNoName() {
        $oldName = "Franek";

        $company = CompanyFactory::createOne();
        EmployeeFactory::createOne(
            [ 
                'name' => $oldName,
                'company' => $company
            ]
        );

        $iri = $this->findIriBy(Employee::class, ['name' => $oldName]);
        static::createClient()->request('PATCH',$iri,[
            'json' => [
                'name' => ''
            ],
            'headers' => [
                'content-type' => 'application/merge-patch+json'
            ]
            ]);

        $this->assertResponseIsUnprocessable();
    }

    public function testDeleteEmployee() {

        $name = "Franek";
        EmployeeFactory::createOne(['name' => $name, 'company' => CompanyFactory::createOne()]);        
        $iri = $this->findIriBy(Employee::class, ["name"=> $name]);
        static::createClient()->request('DELETE',$iri);
        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Company::class)->findOneBy(['name' => $name])
        );
    }

    public function testPutUpdate() {
        $oldName = "Franek";
        $newName = "Dolas";

        $company = CompanyFactory::createOne();
        $employee = EmployeeFactory::createOne(
            [ 
                'name' => $oldName,
                'company' => $company
            ]
        );

        $iri = $this->findIriBy(Employee::class, ['name' => $oldName]);

        $employeeDto = new EmployeeDto();

        $employeeDto->name = $employee->getName();
        $employeeDto->company = $iri;
        $employeeDto->phoneNumber = $employee->getPhoneNumber();
        $employeeDto->surname = $employee->getSurname();
        $employeeDto->email = $employee->getEmail();

        static::createClient()->request('PATCH',$iri,[
            'json' => [
                'name' => $newName
            ],
            'headers' => [
                'content-type' => 'application/merge-patch+json'
            ]
            ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => $newName
        ]);        
    }

    public function testPutUpdateFailWithNoName() {
        $oldName = "Franek";
        $company = CompanyFactory::createOne();
        $employee = EmployeeFactory::createOne(
            [ 
                'name' => $oldName,
                'company' => $company
            ]
        );

        $iri = $this->findIriBy(Employee::class, ['name' => $oldName]);

        $employeeDto = new EmployeeDto();

        $employeeDto->name = $employee->getName();
        $employeeDto->company = $iri;
        $employeeDto->phoneNumber = $employee->getPhoneNumber();
        $employeeDto->surname = $employee->getSurname();
        $employeeDto->email = $employee->getEmail();

        static::createClient()->request('PATCH',$iri,[
            'json' => [
                'name' => ''
            ],
            'headers' => [
                'content-type' => 'application/merge-patch+json'
            ]
            ]);

        $this->assertResponseIsUnprocessable();
    }
    
}
