<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Company;
use App\Entity\Employee;
use Override;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

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
        $data = $request->getContent();
        $entity = $this->deserialize($data); 
        
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
    
    #[Route('/{id}', methods: ['PUT'])]
    public function update(
        Request $request,
        Company $company,
    ): JsonResponse {
         
        $this->serializer->deserialize($request->getContent(), $this->getEntityClass(), 'json', [
            'object_to_populate' => $company,
            'groups' => ['update'],
        ]);
        
        $errors = $this->validator->validate($company);
        if (count($errors) > 0) {
            return new JsonResponse($this->serializer->serialize($errors, 'json'), 400, [], true);
        }

        if ($company->getEmployeers()->count() > 0) {
            
            $ids = [];
            
            foreach($company->getEmployeers() as $employeer) {
                $errors = $this->validator->validate($employeer);
                if (count($errors) > 0) {
                   return new JsonResponse($this->serializer->serialize($errors, 'json'), 400, [], true);
                }
                
                $ids[] = $employeer->getId();
            }
          
            $repository = $this->entityManager->getRepository(Employee::class);
            $employeers = $repository->findBy(['id' => $ids]);
            
            foreach($employeers as $employeer) {
                $oldCompany = $employeer->getCompany();
                $oldCompany->removeEmployeer($employeer);
                $employeer->setCompany($company);
            }        
        }
        
        $this->entityManager->flush();

        return new JsonResponse($this->serialize($company), 200, [], true);
    }
    
    #[Override]
    protected function getEntityClass(): string {
        return Company::class;
    }
}