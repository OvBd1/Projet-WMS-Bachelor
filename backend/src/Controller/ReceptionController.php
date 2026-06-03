<?php

namespace App\Controller;

use App\DTO\LigneReceptionDTO;
use App\DTO\ReceptionDTO;
use App\Entity\Utilisateur;
use App\Repository\ReceptionRepository;
use App\Service\ReceptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/receptions', name: 'api_receptions_')]
class ReceptionController extends AbstractController
{
    public function __construct(
        private ReceptionService $service,
        private ReceptionRepository $repo,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json(
            array_map($this->service->normalizeList(...), $this->repo->findAll())
        );
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $reception = $this->repo->find($id);
        if (!$reception) {
            return $this->json(['message' => 'Réception introuvable.'], 404);
        }
        return $this->json($this->service->normalize($reception));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $dto                = new ReceptionDTO();
        $dto->tiersId       = isset($data['tiersId']) ? (int)$data['tiersId'] : null;
        $dto->dateReception = $data['dateReception'] ?? null;
        $dto->lignes        = array_map(function (array $l): LigneReceptionDTO {
            $ligne               = new LigneReceptionDTO();
            $ligne->articleId    = (int)($l['articleId'] ?? 0);
            $ligne->emplacementId = (int)($l['emplacementId'] ?? 0);
            $ligne->quantite     = (int)($l['quantite'] ?? 0);
            $ligne->dlc          = $l['dlc'] ?? null;
            $ligne->numeroSerie  = $l['numeroSerie'] ?? null;
            return $ligne;
        }, $data['lignes'] ?? []);

        $errors = $this->validator->validate($dto);
        if (\count($errors) > 0) {
            $messages = [];
            foreach ($errors as $e) {
                $messages[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->json(['errors' => $messages], 422);
        }

        /** @var Utilisateur $user */
        $user = $this->getUser();

        try {
            $reception = $this->service->create($dto, $user);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }

        return $this->json($this->service->normalize($reception), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $reception = $this->repo->find($id);
        if (!$reception) {
            return $this->json(['message' => 'Réception introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $dto                = new ReceptionDTO();
        $dto->tiersId       = isset($data['tiersId']) ? (int)$data['tiersId'] : null;
        $dto->dateReception = $data['dateReception'] ?? null;
        $dto->lignes        = array_map(function (array $l): LigneReceptionDTO {
            $ligne                = new LigneReceptionDTO();
            $ligne->articleId     = (int)($l['articleId'] ?? 0);
            $ligne->emplacementId = (int)($l['emplacementId'] ?? 0);
            $ligne->quantite      = (int)($l['quantite'] ?? 0);
            $ligne->dlc           = $l['dlc'] ?? null;
            $ligne->numeroSerie   = $l['numeroSerie'] ?? null;
            return $ligne;
        }, $data['lignes'] ?? []);

        $errors = $this->validator->validate($dto);
        if (\count($errors) > 0) {
            $messages = [];
            foreach ($errors as $e) {
                $messages[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->json(['errors' => $messages], 422);
        }

        try {
            $this->service->update($reception, $dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($this->service->normalize($reception));
    }

    #[Route('/{id}/valider', name: 'valider', methods: ['PATCH'])]
    public function valider(int $id): JsonResponse
    {
        $reception = $this->repo->find($id);
        if (!$reception) {
            return $this->json(['message' => 'Réception introuvable.'], 404);
        }

        try {
            $this->service->valider($reception);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($this->service->normalize($reception));
    }

    #[Route('/{id}/annuler', name: 'annuler', methods: ['PATCH'])]
    public function annuler(int $id): JsonResponse
    {
        $reception = $this->repo->find($id);
        if (!$reception) {
            return $this->json(['message' => 'Réception introuvable.'], 404);
        }

        try {
            $this->service->annuler($reception);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($this->service->normalize($reception));
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $reception = $this->repo->find($id);
        if (!$reception) {
            return $this->json(['message' => 'Réception introuvable.'], 404);
        }

        try {
            $this->service->delete($reception);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json(null, 204);
    }
}
