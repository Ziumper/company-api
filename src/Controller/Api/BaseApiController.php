<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\BaseEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function json_validate;

abstract readonly class BaseApiController 
{
    protected readonly EntityRepository $repository;

    public function __construct(
        protected SerializerInterface $serializer,
        protected ValidatorInterface $validator,
        protected EntityManagerInterface $entityManager,
    )
    {
        $this->repository = $this->entityManager->getRepository($this->getEntityClass());
    }
    
    abstract protected function getEntityClass(): string;
  
    public function remove(BaseEntity $entity): JsonResponse
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new JsonResponse(null, 204);
    }


    public function show(BaseEntity $entity): JsonResponse
    {
        return new JsonResponse(
            data: $this->serialize($entity),
            status: 200,
            json: true,
        );
    }
    
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = min(50, (int)$request->query->get('limit', 10));

        $offset = ($page - 1) * $limit;

        $companies = $this->repository->findBy([], null, $limit, $offset);

        $data = [
            'page' => $page,
            'limit' => $limit,
            'total' => $this->repository->count(),
            'data' => $companies,
        ];

        return new JsonResponse($this->serialize($data), 200, ['content-type' => 'application/json'], true);
    }
    
    public function add(
        Request $request,
        ?BaseEntity &$entity = null,
    ): JsonResponse {
        if (!$this->isJson($request)) {
            return new JsonResponse(['message' => 'Invalid JSON'], 400);
        }
        
        if(!$entity) {
            $data = $request->getContent();
            $entity = $this->deserialize($data);
        }
        
        $errors = $this->validator->validate($entity);
        
        if (count($errors) > 0) {
            return new JsonResponse($this->serializer->serialize($errors, 'json'), 400, [], true);
        }
        
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
               
        return $this->show($entity);
    }
    
    public function patch(Request $request, ?BaseEntity &$entity = null): JsonResponse 
    {
        if (!$this->isJson($request)) {
            return new JsonResponse(['message' => 'Invalid JSON'], 400);
        }
        
        if ($entity) {
            $this->deserialize($request->getContent(), ['update'], $entity);
        }
        
        $errors = $this->validator->validate($entity);
        if (count($errors) > 0) {
            return new JsonResponse($this->serializer->serialize($errors, 'json'), 400, [], true);
        }
        
        $this->entityManager->flush();

        return new JsonResponse($this->serialize($entity), 200, [], true);
    }
    
    protected function serialize(mixed $data, array $groups = ['read']): string 
    {
        return $this->serializer->serialize($data, 'json', [
           AbstractNormalizer::GROUPS => $groups,
           JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE,
        ]);
    }
    
    protected function deserialize(string $data, array $groups = [ 'create'], ?BaseEntity &$entity = null): mixed
    {
        $config = [
            'groups' => $groups,
        ];
        
        if ($entity) {
            $config['object_to_populate'] = $entity;
        }
        
        return  $this->serializer->deserialize($data, $this->getEntityClass(), 'json', $config);
    }
    
    protected function isJson(Request $request): bool {
        return json_validate($request->getContent());
    }
        
}
