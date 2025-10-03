<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Company;
use App\Entity\Employee;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/company')]
class CompanyController
{
    #[Route('', methods:['GET'])]
    public function index(Request $request, CompanyRepository $companyRepository, SerializerInterface $serializer): JsonResponse
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = min(50, (int)$request->query->get('limit', 10));

        $offset = ($page - 1) * $limit;

        $companies = $companyRepository->findBy([], null, $limit, $offset);

        $data = [
            'page' => $page,
            'limit' => $limit,
            'total' => $companyRepository->count(),
            'data' => $companies,
        ];
        
        $json = $serializer->serialize($data, 'json', [
            'groups' => ['read'],
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);

        return new JsonResponse($json, 200, ['content-type' => 'application/json'], true);
    }
    
    #[Route('', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = $request->getContent();
        
        $company = $serializer->deserialize($data, 
                Company::class, 
                'json',
                ['groups' => ['create']]);
        
        $errors = $validator->validate(value: $company);
        
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), 400, [], true);
        }
        
        $em->persist($company);
        $em->flush();
                
        $mydata = $serializer->serialize($company, 'json', 
        [
            'groups' => ['read'],
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);
        
        return new JsonResponse($mydata, 201, [], true);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Company $company, SerializerInterface $serializer): JsonResponse
    {
        return new JsonResponse(
            $json = $serializer->serialize($company, 'json', [
            'groups' => ['read'],
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
             ]),
            200,
            [],
            true
        );
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(
        Request $request,
        Company $company,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
    ): JsonResponse {
        
        $serializer->deserialize($request->getContent(), Company::class, 'json', [
            'object_to_populate' => $company,
            'groups' => ['update'],
        ]);
        
        $errors = $validator->validate($company);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), 400, [], true);
        }

         if ($company->getEmployeers()->count() > 0) {
            $ids = [];
            
            foreach($company->getEmployeers() as $employeer) {
                $ids[] = $employeer->getId();
            }
          
            $repository = $em->getRepository(Employee::class);
            $employeers = $repository->findBy(['id' => $ids]);
            
            foreach($employeers as $employeer) {
                $oldCompany = $employeer->getCompany();
                $oldCompany->removeEmployeer($employeer);
                $employeer->setCompany($company);
            }        
        }
        
        $em->flush();

        $json = $serializer->serialize($company, 'json', [
            'groups' => ['read'],
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);
        
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Company $company, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($company);
        $em->flush();

        return new JsonResponse(null, 204);
    }
}