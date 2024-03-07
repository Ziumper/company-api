<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Dto\CompanyDto;
use App\Factory\CompanyFactory;
use App\Entity\Company;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CompanyTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    private string $apiUrl = "/api/companies";

    public function testGetCollection(): void
    {
        // Create 100 books using our factory
        CompanyFactory::createMany(100);
    
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        $response = static::createClient()->request('GET', $this->apiUrl);

        $this->assertResponseIsSuccessful();
        // Asserts that the returned content type is JSON-LD (the default)
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        // Asserts that the returned JSON is a superset of this one
        $this->assertJsonContains([
            '@context' => '/api/contexts/Company',
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

        // Because test fixtures are automatically loaded between each test, you can assert on them
        $this->assertCount(30, $response->toArray()['hydra:member']);

        // Asserts that the returned JSON is validated by the JSON Schema generated for this resource by API Platform
        // This generated JSON Schema is also used in the OpenAPI spec!
        $this->assertMatchesResourceCollectionJsonSchema(Company::class);
        $this->assertResponseIsSuccessful();
    }

    public function testCreateCompany(): void
    {
        $company = new CompanyDto();

        /**
         * @var \Faker\Generator $faker
         */
        $faker = CompanyFactory::faker();
        $company->name = $faker->name();
        $company->street = $faker->streetAddress();
        $company->taxReferenceNumber = $faker->numerify("##########");
        $company->town = $faker->city();
        $company->zipcode = $faker->postcode();

        $array = json_decode(json_encode($company), true);
        
        /**
         * @var ResponseInterface $response
         */
        $response = static::createClient()->request('POST', $this->apiUrl, 
        [
            'json' => $array,    
            'headers'=> [
                'content-type' => 'application/ld+json'
            ]
            ]);

        $content = $response->toArray();
        
        $this->assertEquals($company->name, $content['name'],"name of company is wrong!");
        $this->assertEquals($company->street, $content['street'], "street is wrong for company!");
        $this->assertEquals($company->zipcode, $content['zipcode'],"zipcode is wrong for company");
        $this->assertEquals($company->town, $content['town'], "town is wrogn for company");
     
        $this->assertResponseIsSuccessful(message:"not succesfull response, from post message");
        $this->assertMatchesResourceItemJsonSchema(Company::class);
    }

    public function testWrongCreateCompanyWithWrongZipCodeFormat(): void
    {
        $company = new CompanyDto();

        /**
         * @var \Faker\Generator $faker
         */
        $faker = CompanyFactory::faker();
        $company->name = $faker->name();
        $company->street = $faker->streetAddress();
        $company->taxReferenceNumber = $faker->numerify("#########"); //9 numbers instead 10
        $company->town = $faker->city();
        $company->zipcode = $faker->postcode();

        $array = json_decode(json_encode($company), true);
        
        static::createClient()->request('POST', $this->apiUrl, 
        [
            'json' => $array,    
            'headers'=> [
                'content-type' => 'application/ld+json'
            ]
            ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testWrongCreateCompanyWithWrongZipCode(): void
    {
        $company = new CompanyDto();

        /**
         * @var \Faker\Generator $faker
         */
        $faker = CompanyFactory::faker();
        $company->name = $faker->name();
        $company->street = $faker->streetAddress();
        $company->taxReferenceNumber = $faker->numerify("##########"); 
        $company->town = $faker->city();
        $company->zipcode = $faker->postcode().$faker->randomDigit();

        $array = json_decode(json_encode($company), true);
        
        static::createClient()->request('POST', $this->apiUrl, 
        [
            'json' => $array,    
            'headers'=> [
                'content-type' => 'application/ld+json'
            ]
            ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testBlankCreateCompany() {

        $companies = [];
        $companies[] = $this->getCompanyWithBlankField("name");
        $companies[] = $this->getCompanyWithBlankField("street");
        $companies[] = $this->getCompanyWithBlankField("taxReferenceNumber");
        $companies[] = $this->getCompanyWithBlankField("town");
        $companies[] = $this->getCompanyWithBlankField("zipcode");
        
        foreach( $companies as $company ) {

            $response = static::createClient()->request('POST', $this->apiUrl, 
            [
                'json' => $company,    
                'headers'=> [
                    'content-type' => 'application/ld+json'
                ]
            ]);
            

            $this->assertEquals(422, $response->getStatusCode());
        }
    }

    public function testPartialUpdate() {
         // Only create the book we need with a given ISBN
         $oldName = "Old Company S.A.";
         $newName = "New Company S.A";
         CompanyFactory::createOne(['name' => $oldName]);
    
         $iri = $this->findIriBy(Company::class, ['name' => $oldName]);
         static::createClient()->request('PATCH', $iri, [
             'json' => [
                 'name' => $newName,
             ],
             'headers' => [
                 'Content-Type' => 'application/merge-patch+json',
             ]           
         ]);
 
         $this->assertResponseIsSuccessful();
         $this->assertJsonContains([
             'name' => $newName,
         ]);
    }   

    public function testPartialUpdateCompanyFailBecauseOfEmptyNameValue() {
        // Only create the book we need with a given ISBN
        $oldName = "Old Company S.A.";
        $newName = "New Company S.A";
        CompanyFactory::createOne(['name' => $oldName]);
   
        $iri = $this->findIriBy(Company::class, ['name' => $oldName]);
        static::createClient()->request('PATCH', $iri, [
            'json' => [
                'name' => '',
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ]           
        ]);

        $this->assertResponseIsUnprocessable();
   }   

    
    public function testDeleteBook(): void
    {
        $companyName = "My Company S.A";
        CompanyFactory::createOne(['name' => $companyName]);
        
        $iri = $this->findIriBy(Company::class, ['name' => $companyName]);
        static::createClient()->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Company::class)->findOneBy(['name' => $companyName])
        );
    }

    private function getCompanyWithBlankField(string $name): array {
        $company = new CompanyDto();

        /**
         * @var \Faker\Generator $faker
         */
        $faker = CompanyFactory::faker();
        $company->name = $faker->name();
        $company->street = $faker->streetAddress();
        $company->taxReferenceNumber = $faker->numerify("##########"); 
        $company->town = $faker->city();
        $company->zipcode = $faker->postcode();

        $array = json_decode(json_encode($company), true);

        $array[$name] = "";

        return $array;
    }
  
}
