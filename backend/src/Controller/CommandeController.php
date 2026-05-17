<?php

namespace App\Controller;

use App\DTO\CommandeDTO;
use App\DTO\CommandeStatutDTO;
use App\DTO\LigneCommandeDTO;
use App\Entity\Utilisateur;
use App\Repository\CommandeRepository;
use App\Service\CommandeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/commandes', name: 'api_commandes_')]
class CommandeController extends AbstractController
{
    public function __construct(
        private CommandeService $service,
        private CommandeRepository $repo,
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
        $commande = $this->repo->find($id);
        if (!$commande) {
            return $this->json(['message' => 'Commande introuvable.'], 404);
        }
        return $this->json($this->service->normalize($commande));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $dto = new CommandeDTO();
        $dto->lignes = array_map(function (array $l): LigneCommandeDTO {
            $ligne           = new LigneCommandeDTO();
            $ligne->articleId = (int)($l['articleId'] ?? 0);
            $ligne->quantite  = (int)($l['quantite'] ?? 0);
            return $ligne;
        }, $data['lignes'] ?? []);

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $e) {
                $messages[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->json(['errors' => $messages], 422);
        }

        /** @var Utilisateur $user */
        $user = $this->getUser();

        try {
            $commande = $this->service->create($dto, $user);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }

        return $this->json($this->service->normalize($commande), 201);
    }

    #[Route('/{id}/statut', name: 'update_statut', methods: ['PATCH'])]
    public function updateStatut(int $id, Request $request): JsonResponse
    {
        $commande = $this->repo->find($id);
        if (!$commande) {
            return $this->json(['message' => 'Commande introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto  = new CommandeStatutDTO();
        $dto->statut = $data['statut'] ?? '';

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $e) {
                $messages[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->json(['errors' => $messages], 422);
        }

        $commande = $this->service->updateStatut($commande, $dto);

        return $this->json($this->service->normalize($commande));
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $commande = $this->repo->find($id);
        if (!$commande) {
            return $this->json(['message' => 'Commande introuvable.'], 404);
        }
        $this->service->delete($commande);
        return $this->json(null, 204);
    }
}
