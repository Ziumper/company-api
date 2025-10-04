<?php

declare (strict_types=1);

namespace App\Controller\Api;

use App\Entity\Employee;
use Override;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/employee')]
readonly class EmployeeController extends BaseApiController {
    
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Employee $employee): JsonResponse
    {
       return $this->remove($employee);
    }
    
    #[Route('/{id}', methods: ['GET'])]
    public function get(Employee $employee): JsonResponse 
    {
        return $this->show($employee);
    }
    
    #[Route('', methods:['GET'])]
    public function index(Request $request): JsonResponse  
    {
        return $this->list($request);
    }
    
    
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse 
    {
        return $this->add($request);
    }
    
    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(
        Request $request,
        Employee $employee,
    ): JsonResponse {
        return $this->patch($request, $employee);
    }
    
    #[Override]
    protected function getEntityClass(): string {
        return Employee::class;
    }
}
