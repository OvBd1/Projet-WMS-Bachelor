<?php

namespace App\Controller;

use App\DTO\RegisterDTO;
use App\Repository\UtilisateurRepository;
use App\Service\UtilisateurService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class UtilisateurController extends AbstractController
{
    public function __construct(
        private UtilisateurService $service,
        private UtilisateurRepository $repo,
        private ValidatorInterface $validator
    ) {}

    #[Route('/auth/register', name: 'auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $dto = new RegisterDTO();
        $dto->email    = $data['email'] ?? '';
        $dto->password = $data['password'] ?? '';
        $dto->role     = $data['role'] ?? 'ROLE_USER';

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->validationError($errors);
        }

        try {
            $user = $this->service->register($dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($this->service->normalize($user), 201);
    }

    #[Route('/utilisateurs', name: 'utilisateurs_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json(
            array_map($this->service->normalize(...), $this->repo->findAll())
        );
    }

    #[Route('/utilisateurs/{id}', name: 'utilisateurs_get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $user = $this->repo->find($id);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur introuvable.'], 404);
        }
        return $this->json($this->service->normalize($user));
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
