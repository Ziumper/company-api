<?php

declare (strict_types=1);

namespace App\Controller\Api;

use App\Entity\Company;
use App\Entity\Employee;
use Override;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/employee')]
readonly class EmployeeController extends BaseApiController
{
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
        if (!$this->isJson($request)) {
            return new JsonResponse(['message' => 'Invalid JSON'], 400);
        }

        $payload = $request->toArray();

        if (!isset($payload['company']) || !is_array($payload['company'])) {
            return new JsonResponse(['message' => 'Missing required company argument'], 400);
        }

        $companyData = $payload['company'];

        if (!isset($companyData['id']) || !is_numeric($companyData['id']) || (int)$companyData['id'] <= 0) {
            return new JsonResponse(['message' => 'Invalid company id'], 400);
        }

        $companyId = (int) $companyData['id'];
        $detachedCompany = $this->entityManager->find(Company::class, $companyId);

        if (!$detachedCompany) {
            return new JsonResponse(['message' => 'Company not found'], 404);
        }

        $employee = $this->deserialize($request->getContent());
        $employee->setCompany($detachedCompany);

        return $this->add($request, $employee);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(
        Request $request,
        Employee $employee,
    ): JsonResponse {
        return $this->patch($request, $employee);
    }

    #[Override]
    protected function getEntityClass(): string
    {
        return Employee::class;
    }
}
