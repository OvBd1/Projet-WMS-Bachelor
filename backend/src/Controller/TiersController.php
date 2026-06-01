<?php

namespace App\Controller;

use App\DTO\TiersDTO;
use App\Repository\TiersRepository;
use App\Service\TiersService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tiers', name: 'api_tiers_')]
class TiersController extends AbstractController
{
    public function __construct(
        private TiersService $service,
        private TiersRepository $repo,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json(array_map($this->service->normalize(...), $this->repo->findAll()));
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $tiers = $this->repo->find($id);
        if (!$tiers) {
            return $this->json(['message' => 'Tiers introuvable.'], 404);
        }
        return $this->json($this->service->normalize($tiers));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto  = $this->buildDto($data);

        $errors = $this->validator->validate($dto);
        if (\count($errors) > 0) {
            $messages = [];
            foreach ($errors as $e) {
                $messages[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->json(['errors' => $messages], 422);
        }

        return $this->json($this->service->normalize($this->service->create($dto)), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $tiers = $this->repo->find($id);
        if (!$tiers) {
            return $this->json(['message' => 'Tiers introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto  = $this->buildDto($data);

        $errors = $this->validator->validate($dto);
        if (\count($errors) > 0) {
            $messages = [];
            foreach ($errors as $e) {
                $messages[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->json(['errors' => $messages], 422);
        }

        return $this->json($this->service->normalize($this->service->update($tiers, $dto)));
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $tiers = $this->repo->find($id);
        if (!$tiers) {
            return $this->json(['message' => 'Tiers introuvable.'], 404);
        }
        $this->service->delete($tiers);
        return $this->json(null, 204);
    }

    private function buildDto(array $data): TiersDTO
    {
        $dto            = new TiersDTO();
        $dto->nom       = trim($data['nom'] ?? '');
        $dto->type      = $data['type'] ?? 'FOURNISSEUR';
        $dto->email     = $data['email'] ?: null;
        $dto->telephone = $data['telephone'] ?: null;
        $dto->adresse   = $data['adresse'] ?: null;
        return $dto;
    }
}
