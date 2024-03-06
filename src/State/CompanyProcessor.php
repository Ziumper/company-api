<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use App\Dto\CompanyDto;
use App\Entity\Company;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
/*
*
*@implements ProcessorInterface<CompanyDto,Company>
*
*/
class CompanyProcessor implements  ProcessorInterface {

    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
    ) {}


    /**
     *  @return Company
     */
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, $uriVariables = [], $context = []) 
    {
        $company = new Company();
        
        $company->setName($data->name);
        $company->setTaxReferenceNumber($data->taxReferenceNumber);
        $company->setTown($data->town);
        $company->setStreet($data->street);
        $company->setZipcode($data->zipcode);

        $company->setCreatedAt(\DateTimeImmutable::createFromMutable(new \DateTime("now")));
        $company->setUpdatedAt(\DateTimeImmutable::createFromMutable(new \DateTime("now")));

        $result = $this->persistProcessor->process($company,$operation,$uriVariables,$context);
        return $result;
    }
}