<?php

namespace App\Controller;

use App\DTO\EmplacementDTO;
use App\Repository\EmplacementRepository;
use App\Service\EmplacementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/emplacements', name: 'api_emplacements_')]
class EmplacementController extends AbstractController
{
    public function __construct(
        private EmplacementService $service,
        private EmplacementRepository $repo,
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
        $e = $this->repo->find($id);
        if (!$e) {
            return $this->json(['message' => 'Emplacement introuvable.'], 404);
        }
        return $this->json($this->service->normalize($e));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto  = $this->buildDto($data);

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->validationError($errors);
        }

        try {
            $emplacement = $this->service->create($dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($this->service->normalize($emplacement), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $emplacement = $this->repo->find($id);
        if (!$emplacement) {
            return $this->json(['message' => 'Emplacement introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto  = $this->buildDto($data);

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->validationError($errors);
        }

        try {
            $emplacement = $this->service->update($emplacement, $dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($this->service->normalize($emplacement));
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $emplacement = $this->repo->find($id);
        if (!$emplacement) {
            return $this->json(['message' => 'Emplacement introuvable.'], 404);
        }
        $this->service->delete($emplacement);
        return $this->json(null, 204);
    }

    private function buildDto(array $data): EmplacementDTO
    {
        $dto = new EmplacementDTO();
        $dto->code               = $data['code'] ?? '';
        $dto->description        = $data['description'] ?? null;
        $dto->typeEmplacementId  = (int)($data['typeEmplacementId'] ?? 0);
        return $dto;
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
