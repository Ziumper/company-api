<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Company;
use App\Entity\Employee;
use Override;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[Route('/api/company')]
readonly class CompanyController extends BaseApiController
{   
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Company $company): JsonResponse
    {
       return $this->remove($company);
    }
    
    #[Route('/{id}', methods: ['GET'])]
    public function get(Company $company): JsonResponse 
    {
        return $this->show($company);
    }
    
    #[Route('', methods:['GET'])]
    public function index(Request $request): JsonResponse  
    {
        return $this->list($request);
    }
    
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse 
    {
        if (!$this->isJson($request)) {
            return new JsonResponse(['message' => 'Invalid JSON'], 400);
        }
        
        $data = $request->getContent();
        $entity = $this->deserialize($data); 
        
        //pre handler of validate inner class
        if ($entity->getEmployeers()->count() > 0) {
            foreach($entity->getEmployeers() as $employeer) {
                $errors = $this->validator->validate($employeer);
                if (count($errors) > 0) {
                   return new JsonResponse($this->serializer->serialize($errors, 'json'), 400, [], true);
                }
            }
        }
        
        return $this->add($request);
    }
    
    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(
        Request $request,
        Company $company,
    ): JsonResponse {
        if (!$this->isJson($request)) {
            return new JsonResponse(['message' => 'Invalid JSON'], 400);
        }

        $payload = $request->toArray();
        
        //pre handler validate and update inner class
        if (!empty($payload['employeers'])) {
            
            $ids = [];
            $serializedEmployeers = [];
            
            foreach($payload['employeers'] as $employeer) {
                $ids[] = $employeer['id'];
                $serializedEmployeers[$employeer['id']] = json_encode($employeer);
            }
            
            $repository = $this->entityManager->getRepository(Employee::class);
            $employeers = $repository->findBy(['id' => $ids]);
            
            foreach($employeers as $employeer) {
                $this->serializer->deserialize(
                    data: $serializedEmployeers[$employeer->getId()], 
                    type: Employee::class, 
                    format: 'json',
                    context: [
                        AbstractNormalizer::GROUPS => 'update',
                        AbstractNormalizer::OBJECT_TO_POPULATE => $employeer,
                        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                        AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
                    ]
                ); 
                
                $errors = $this->validator->validate($employeer);
                if (count($errors) > 0) {
                   return new JsonResponse($this->serializer->serialize($errors, 'json'), 400, [], true);
                }
                
                $oldCompany = $employeer->getCompany();
                $oldCompany->removeEmployeer($employeer);
                $employeer->setCompany($company);
                $company->addEmployeer($employeer);
            }        
        }
        
        return $this->patch($request, $company);
    }
    
    #[Override]
    protected function getEntityClass(): string {
        return Company::class;
    }
}