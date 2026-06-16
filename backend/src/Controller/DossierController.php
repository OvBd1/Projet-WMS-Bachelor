<?php

namespace App\Controller;

use App\DTO\DossierDTO;
use App\Repository\DossierRepository;
use App\Service\DossierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/dossiers', name: 'api_dossiers_')]
class DossierController extends AbstractController
{
    public function __construct(
        private DossierService $service,
        private DossierRepository $repo,
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
        $dossier = $this->repo->find($id);
        if (!$dossier) {
            return $this->json(['message' => 'Dossier introuvable.'], 404);
        }
        return $this->json($this->service->normalize($dossier));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $dto  = $this->buildDto($data);

        $errors = $this->validator->validate($dto);
        if (\count($errors) > 0) {
            return $this->validationError($errors);
        }

        return $this->json($this->service->normalize($this->service->create($dto, $this->getUser())), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $dossier = $this->repo->find($id);
        if (!$dossier) {
            return $this->json(['message' => 'Dossier introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto  = $this->buildDto($data);

        $errors = $this->validator->validate($dto);
        if (\count($errors) > 0) {
            return $this->validationError($errors);
        }

        return $this->json($this->service->normalize($this->service->update($dossier, $dto, $this->getUser())));
    }

    private function buildDto(array $data): DossierDTO
    {
        $dto                = new DossierDTO();
        $dto->code          = trim($data['code'] ?? '');
        $dto->raisonSociale = trim($data['raisonSociale'] ?? '');
        $dto->rue           = ($data['rue'] ?? '') ?: null;
        $dto->codePostal    = ($data['codePostal'] ?? '') ?: null;
        $dto->ville         = ($data['ville'] ?? '') ?: null;
        $dto->pays          = ($data['pays'] ?? '') ?: null;
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
