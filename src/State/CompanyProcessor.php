<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use \Symfony\Component\Validator\Validator\ValidatorInterface;

/*
*
*@implements ProcessorInterface<CompanyDto,Company>
*
*/
class CompanyProcessor implements  ProcessorInterface {

    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private CreatedUpdatedAtProcessor $createdUpdatedAtProcessor,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager
    ) {}


    /**
     *  @return Company
     */
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, $uriVariables = [], $context = []): Company
    {
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
             * @var Company $company
             */
            $company = $this->entityManager->getRepository(Company::class)->find($uriVariables['id']);
            foreach($data as $key => $value) {
                $setfunctionName = 'set'.ucfirst($key);
                $company->$setfunctionName($value);
            }
            
            $data = $company;

            return $this->updateAndSave($data, $operation, $uriVariables, $context);
        }

        if($operation instanceof \ApiPlatform\Metadata\Post) {
            $company = new Company();
        } else {
            $company = $this->entityManager->getRepository(Company::class)->find($uriVariables['id']);
        }

        $company->setName($data->name);
        $company->setTaxReferenceNumber($data->taxReferenceNumber);
        $company->setTown($data->town);
        $company->setStreet($data->street);
        $company->setZipcode($data->zipcode);
        $data = $company;
        
        return $this->updateAndSave($data, $operation, $uriVariables,$context);
    }

    private function updateAndSave(mixed $data, $operation, $uriVariables, $context): Company {
        $result = $this->createdUpdatedAtProcessor->process($data, $operation, $uriVariables, $context);
        return $this->persistProcessor->process($result,$operation,$uriVariables,$context);
    }
}