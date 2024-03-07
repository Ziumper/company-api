<?php

namespace App\State;


use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ApiPlatform\Api\IriConverterInterface;
use App\Entity\Company;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmployeeProcessor implements ProcessorInterface 
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')] 
        private ProcessorInterface $persistProcessor,
        private CreatedUpdatedAtProcessor $createdUpdatedAtProcessor,
        private IriConverterInterface $iriConverter,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, $uriVariables = [], $context = []): Employee {
        if($operation instanceof \ApiPlatform\Metadata\Patch) {
            //validation
            foreach($data as $key => $value){
                $violations = $this->validator->validateProperty($data,$key,"postValidation");
                if (\count($violations)) {
                    throw new HttpException(422, implode("\n", array_map(static fn ($e) => $e->getMessage(), iterator_to_array($violations))), new ValidationFailedException($data, $violations));
                }
            }

            //partial set based on key value - invoke method
            /**
             * @var Employee $employee
             */
            $employee = $this->entityManager->getRepository(Employee::class)->find($uriVariables['id']);
            foreach($data as $key => $value) {
                if($key === "company") {
                    echo " here we gooooo!";
                    $value = $this->iriConverter->getResourceFromIri($data->company,$context,$operation); 
                } 
                    
                $setfunctionName = 'set'.ucfirst($key);
                $employee->$setfunctionName($value);
            }
            
            $data = $employee;
        }

        if($operation instanceof \ApiPlatform\Metadata\Put) {
            
            $employee = $this->entityManager->getRepository(Employee::class)->find($uriVariables['id']);
            /**
             * @var Company $resource
             */
            $resource = $this->iriConverter->getResourceFromIri($data->company,$context,$operation); 
            $employee->setCompany($resource);
            $employee->setName($data->name);
            $employee->setPhoneNumber($data->phoneNumber);
            $employee->setSurname($data->surname);
            $employee->setEmail($data->email);
            $data = $employee;
        }

        if($operation instanceof Post)  {
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
            $data = $employee;
        }

        $result = $this->createdUpdatedAtProcessor->process($data, $operation, $uriVariables, $context);
        return $this->persistProcessor->process($result, $operation, $uriVariables, $context);
    }
}