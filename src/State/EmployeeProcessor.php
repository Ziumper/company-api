<?php

namespace App\State;


use ApiPlatform\State\ProcessorInterface;
use App\Entity\Employee;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ApiPlatform\Api\IriConverterInterface;
use App\Entity\Company;

class EmployeeProcessor implements ProcessorInterface 
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')] 
        private ProcessorInterface $persistProcessor,
        private CreatedUpdatedAtProcessor $createdUpdatedAtProcessor,
        private IriConverterInterface $iriConverter
    ) {}


    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, $uriVariables = [], $context = []): Employee {
        /**
         * @var Company $resource
         */
        $resource = $this->iriConverter->getResourceFromIri($data->company,$context,$operation); 

        $employee = new Employee();

        $employee->setCompany($resource);
        $employee->setName($data->name);
        $employee->setPhoneNumber($data->phoneNumber);
        $employee->setSurname($data->surname);
        $employee->setEmail($data->email);
    
        $result = $this->createdUpdatedAtProcessor->process($employee, $operation, $uriVariables, $context);
        return $this->persistProcessor->process($result, $operation, $uriVariables, $context);
    }
}