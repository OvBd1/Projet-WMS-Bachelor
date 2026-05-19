<?php

namespace App\Controller;

use App\DTO\TransfertDTO;
use App\Entity\Utilisateur;
use App\Repository\TransfertEmplacementRepository;
use App\Service\TransfertService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/transferts', name: 'api_transferts_')]
class TransfertController extends AbstractController
{
    public function __construct(
        private TransfertService $service,
        private TransfertEmplacementRepository $repo,
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
        $transfert = $this->repo->find($id);
        if (!$transfert) {
            return $this->json(['message' => 'Transfert introuvable.'], 404);
        }
        return $this->json($this->service->normalize($transfert));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $dto = new TransfertDTO();
        $dto->articleId               = (int)($data['articleId'] ?? 0);
        $dto->emplacementSourceId     = (int)($data['emplacementSourceId'] ?? 0);
        $dto->emplacementDestinationId = (int)($data['emplacementDestinationId'] ?? 0);
        $dto->quantite                = (int)($data['quantite'] ?? 0);

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
            $transfert = $this->service->create($dto, $user);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }

        return $this->json($this->service->normalize($transfert), 201);
    }
}
