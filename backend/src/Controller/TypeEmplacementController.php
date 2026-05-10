<?php

namespace App\Controller;

use App\DTO\TypeEmplacementDTO;
use App\Repository\TypeEmplacementRepository;
use App\Service\TypeEmplacementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/types-emplacement', name: 'api_types_emplacement_')]
class TypeEmplacementController extends AbstractController
{
    public function __construct(
        private TypeEmplacementService $service,
        private TypeEmplacementRepository $repo,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json(
            array_map($this->service->normalize(...), $this->repo->findAll())
        );
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $type = $this->repo->find($id);
        if (!$type) {
            return $this->json(['message' => 'TypeEmplacement introuvable.'], 404);
        }
        return $this->json($this->service->normalize($type));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto  = new TypeEmplacementDTO();
        $dto->libelle = $data['libelle'] ?? '';

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->validationError($errors);
        }

        return $this->json($this->service->normalize($this->service->create($dto)), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $type = $this->repo->find($id);
        if (!$type) {
            return $this->json(['message' => 'TypeEmplacement introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto  = new TypeEmplacementDTO();
        $dto->libelle = $data['libelle'] ?? '';

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->validationError($errors);
        }

        return $this->json($this->service->normalize($this->service->update($type, $dto)));
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $type = $this->repo->find($id);
        if (!$type) {
            return $this->json(['message' => 'TypeEmplacement introuvable.'], 404);
        }
        $this->service->delete($type);
        return $this->json(null, 204);
    }

    private function validationError($errors): JsonResponse
    {
        $messages = [];
        foreach ($errors as $e) {
            $messages[$e->getPropertyPath()] = $e->getMessage();
        }
        return $this->json(['errors' => $messages], 422);
    }
}
