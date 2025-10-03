<?php

namespace App\Tests;

use App\Entity\Company;
use App\Factory\CompanyFactory;
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
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');
        $this->assertResponseIsSuccessful();
    }

//    public function testCreateCompany(): void
//    {
//        $company = new Company();
//
//        /**
//         * @var Generator $faker
//         */
//        $faker = CompanyFactory::faker();
//        $company->setName($faker->name());
//        $company->setStreet($faker->streetAddress()); 
//        $company->setTaxReferenceNumber($faker->numerify("##########")); 
//        $company->setTown($faker->city());
//        $company->setZipcode($faker->postcode()); 
//
//        $array = json_decode(json_encode($company), true);
//        
//        /**
//         * @var ResponseInterface $response
//         */
//        $response = static::createClient()->request('POST', $this->apiUrl, 
//        [
//            'json' => $array,    
//            'headers'=> [
//                'content-type' => 'application/ld+json'
//            ]
//            ]);
//
//        $content = $response->toArray();
//        
//        $this->assertEquals($company->name, $content['name'],"name of company is wrong!");
//        $this->assertEquals($company->street, $content['street'], "street is wrong for company!");
//        $this->assertEquals($company->zipcode, $content['zipcode'],"zipcode is wrong for company");
//        $this->assertEquals($company->town, $content['town'], "town is wrogn for company");
//     
//        $this->assertResponseIsSuccessful(message:"not succesfull response, from post message");
//        $this->assertMatchesResourceItemJsonSchema(Company::class);
//    }

//    public function testWrongCreateCompanyWithWrongZipCodeFormat(): void
//    {
//        $company = new CompanyDto();
//
//        /**
//         * @var \Faker\Generator $faker
//         */
//        $faker = CompanyFactory::faker();
//        $company->name = $faker->name();
//        $company->street = $faker->streetAddress();
//        $company->taxReferenceNumber = $faker->numerify("#########"); //9 numbers instead 10
//        $company->town = $faker->city();
//        $company->zipcode = $faker->postcode();
//
//        $array = json_decode(json_encode($company), true);
//        
//        static::createClient()->request('POST', $this->apiUrl, 
//        [
//            'json' => $array,    
//            'headers'=> [
//                'content-type' => 'application/ld+json'
//            ]
//            ]);
//
//        $this->assertResponseStatusCodeSame(422);
//    }
//
//    public function testWrongCreateCompanyWithWrongZipCode(): void
//    {
//        $company = new CompanyDto();
//
//        /**
//         * @var \Faker\Generator $faker
//         */
//        $faker = CompanyFactory::faker();
//        $company->name = $faker->name();
//        $company->street = $faker->streetAddress();
//        $company->taxReferenceNumber = $faker->numerify("##########"); 
//        $company->town = $faker->city();
//        $company->zipcode = $faker->postcode().$faker->randomDigit();
//
//        $array = json_decode(json_encode($company), true);
//        
//        static::createClient()->request('POST', $this->apiUrl, 
//        [
//            'json' => $array,    
//            'headers'=> [
//                'content-type' => 'application/ld+json'
//            ]
//            ]);
//
//        $this->assertResponseStatusCodeSame(422);
//    }
//
//    public function testBlankCreateCompany() {
//
//        $companies = [];
//        $companies[] = $this->getCompanyWithBlankField("name");
//        $companies[] = $this->getCompanyWithBlankField("street");
//        $companies[] = $this->getCompanyWithBlankField("taxReferenceNumber");
//        $companies[] = $this->getCompanyWithBlankField("town");
//        $companies[] = $this->getCompanyWithBlankField("zipcode");
//        
//        foreach( $companies as $company ) {
//
//            $response = static::createClient()->request('POST', $this->apiUrl, 
//            [
//                'json' => $company,    
//                'headers'=> [
//                    'content-type' => 'application/ld+json'
//                ]
//            ]);
//            
//
//            $this->assertEquals(422, $response->getStatusCode());
//        }
//    }
//
//    public function testPutUpdate() {
//        $oldName = "Old Company S.A.";
//        $newName = "New Company S.A";
//        $company = CompanyFactory::createOne(['name' => $oldName]);
//   
//        $companyDto = new CompanyDto();
//        $companyDto->name = $newName;
//        $companyDto->street = $company->getStreet();
//        $companyDto->town = $company->getTown();
//        $companyDto->taxReferenceNumber = $company->getTaxReferenceNumber();
//        $companyDto->zipcode = $company->getZipcode();
//
//
//        $array = json_decode(json_encode($companyDto), true);
//
//        $iri = $this->findIriBy(Company::class, ["name" => $oldName]);
//        static::createClient()->request('PUT', $iri, 
//        [
//            'json' => $array,
//            'headers' => [
//                'Content-Type' => 'application/ld+json',
//            ]           
//        ]);
//
//        $this->assertResponseIsSuccessful();
//        $this->assertJsonContains([
//            'name' => $newName,
//        ]);
//    }
//
//    public function testPutUpdateFailWithEmptyName() {
//        $oldName = "Old Company S.A.";
//        $newName = "New Company S.A";
//        $company = CompanyFactory::createOne(['name' => $oldName]);
//   
//        $companyDto = new CompanyDto();
//        $companyDto->name = '';
//        $companyDto->street = $company->getStreet();
//        $companyDto->town = $company->getTown();
//        $companyDto->taxReferenceNumber = $company->getTaxReferenceNumber();
//        $companyDto->zipcode = $company->getZipcode();
//
//
//        $array = json_decode(json_encode($companyDto), true);
//
//        $iri = $this->findIriBy(Company::class, ["name" => $oldName]);
//        static::createClient()->request('PUT', $iri, 
//        [
//            'json' => $array,
//            'headers' => [
//                'Content-Type' => 'application/ld+json',
//            ]           
//        ]);
//
//        $this->assertResponseIsUnprocessable();
//    }
//
//    public function testPartialUpdate() {
//         $oldName = "Old Company S.A.";
//         $newName = "New Company S.A";
//         CompanyFactory::createOne(['name' => $oldName]);
//    
//         $iri = $this->findIriBy(Company::class, ['name' => $oldName]);
//         static::createClient()->request('PATCH', $iri, [
//             'json' => [
//                 'name' => $newName,
//             ],
//             'headers' => [
//                 'Content-Type' => 'application/merge-patch+json',
//             ]           
//         ]);
// 
//         $this->assertResponseIsSuccessful();
//         $this->assertJsonContains([
//             'name' => $newName,
//         ]);
//    }   
//
//    public function testPartialUpdateCompanyFailBecauseOfEmptyNameValue() {
//        $oldName = "Old Company S.A.";
//        CompanyFactory::createOne(['name' => $oldName]);
//   
//        $iri = $this->findIriBy(Company::class, ['name' => $oldName]);
//        static::createClient()->request('PATCH', $iri, [
//            'json' => [
//                'name' => '',
//            ],
//            'headers' => [
//                'Content-Type' => 'application/merge-patch+json',
//            ]           
//        ]);
//
//        $this->assertResponseIsUnprocessable();
//   }   
//
//    
//    public function testDeleteBook(): void
//    {
//        $companyName = "My Company S.A";
//        CompanyFactory::createOne(['name' => $companyName]);
//        
//        $iri = $this->findIriBy(Company::class, ['name' => $companyName]);
//        static::createClient()->request('DELETE', $iri);
//
//        $this->assertResponseStatusCodeSame(204);
//        $this->assertNull(
//            static::getContainer()->get('doctrine')->getRepository(Company::class)->findOneBy(['name' => $companyName])
//        );
//    }
//
//    private function getCompanyWithBlankField(string $name): array {
//        $company = new CompanyDto();
//
//        /**
//         * @var \Faker\Generator $faker
//         */
//        $faker = CompanyFactory::faker();
//        $company->name = $faker->name();
//        $company->street = $faker->streetAddress();
//        $company->taxReferenceNumber = $faker->numerify("##########"); 
//        $company->town = $faker->city();
//        $company->zipcode = $faker->postcode();
//
//        $array = json_decode(json_encode($company), true);
//
//        $array[$name] = "";
//
//        return $array;
//    }
  
}
