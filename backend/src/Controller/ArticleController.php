<?php

namespace App\Controller;

use App\DTO\ArticleDTO;
use App\Repository\ArticleRepository;
use App\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/articles', name: 'api_articles_')]
class ArticleController extends AbstractController
{
    public function __construct(
        private ArticleService $service,
        private ArticleRepository $repo,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json(
            array_map(fn($a) => $this->service->normalize($a), $this->repo->findAll())
        );
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $article = $this->repo->find($id);
        if (!$article) {
            return $this->json(['message' => 'Article introuvable.'], 404);
        }
        return $this->json($this->service->normalize($article, true));
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

        try {
            $article = $this->service->create($dto, $this->getUser());
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($this->service->normalize($article), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $article = $this->repo->find($id);
        if (!$article) {
            return $this->json(['message' => 'Article introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto  = $this->buildDto($data);

        $errors = $this->validator->validate($dto);
        if (\count($errors) > 0) {
            return $this->validationError($errors);
        }

        try {
            $article = $this->service->update($article, $dto, $this->getUser());
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($this->service->normalize($article));
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $article = $this->repo->find($id);
        if (!$article) {
            return $this->json(['message' => 'Article introuvable.'], 404);
        }

        $this->service->delete($article);

        return $this->json(null, 204);
    }

    #[Route('/{id}/image', name: 'upload_image', methods: ['POST'])]
    public function uploadImage(int $id, Request $request): JsonResponse
    {
        $article = $this->repo->find($id);
        if (!$article) {
            return $this->json(['message' => 'Article introuvable.'], 404);
        }

        $file = $request->files->get('image');
        if (!$file) {
            return $this->json(['message' => 'Aucun fichier fourni.'], 400);
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!\in_array($file->getMimeType(), $allowed, true)) {
            return $this->json(['message' => 'Format non autorisé (jpeg, png, webp, gif uniquement).'], 400);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->json(['message' => 'Fichier trop volumineux (5 Mo max).'], 400);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/articles';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        // Remove old image if present
        $oldPath = $article->getImagePath();
        if ($oldPath) {
            $oldFile = $this->getParameter('kernel.project_dir') . '/public' . $oldPath;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $extension = $file->guessExtension() ?? 'jpg';
        $filename  = 'article-' . $article->getId() . '-' . uniqid() . '.' . $extension;
        $file->move($uploadDir, $filename);

        $this->service->setImage($article, '/uploads/articles/' . $filename);

        return $this->json($this->service->normalize($article));
    }

    private function buildDto(array $data): ArticleDTO
    {
        $dto                       = new ArticleDTO();
        $dto->reference            = $data['reference'] ?? '';
        $dto->libelle              = $data['libelle'] ?? '';
        $dto->description          = $data['description'] ?? null;
        $dto->gestionDlc           = (bool) ($data['gestionDlc'] ?? false);
        $dto->gestionNumeroSerie   = (bool) ($data['gestionNumeroSerie'] ?? false);
        $dto->typeConditionnementId = isset($data['typeConditionnementId']) ? (int) $data['typeConditionnementId'] : null;
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
