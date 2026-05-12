<?php

namespace App\Controller;

use App\DTO\StockUpdateDTO;
use App\Repository\ArticleRepository;
use App\Repository\EmplacementRepository;
use App\Repository\StockRepository;
use App\Service\StockService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/stocks', name: 'api_stocks_')]
class StockController extends AbstractController
{
    public function __construct(
        private StockService $service,
        private StockRepository $repo,
        private ArticleRepository $articleRepo,
        private EmplacementRepository $emplacementRepo,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json(
            array_map($this->service->normalize(...), $this->repo->findAllWithJoins())
        );
    }

    #[Route('/article/{id}', name: 'by_article', methods: ['GET'])]
    public function byArticle(int $id): JsonResponse
    {
        $article = $this->articleRepo->find($id);
        if (!$article) {
            return $this->json(['message' => 'Article introuvable.'], 404);
        }

        $stocks = $this->repo->findBy(['article' => $article]);
        return $this->json(array_map($this->service->normalize(...), $stocks));
    }

    #[Route('/emplacement/{id}', name: 'by_emplacement', methods: ['GET'])]
    public function byEmplacement(int $id): JsonResponse
    {
        $emplacement = $this->emplacementRepo->find($id);
        if (!$emplacement) {
            return $this->json(['message' => 'Emplacement introuvable.'], 404);
        }

        $stocks = $this->repo->findBy(['emplacement' => $emplacement]);
        return $this->json(array_map($this->service->normalize(...), $stocks));
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $stock = $this->repo->find($id);
        if (!$stock) {
            return $this->json(['message' => 'Stock introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $dto  = new StockUpdateDTO();
        $dto->quantite = (int)($data['quantite'] ?? -1);

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $e) {
                $messages[$e->getPropertyPath()] = $e->getMessage();
            }
            return $this->json(['errors' => $messages], 422);
        }

        $stock->setQuantite($dto->quantite);

        return $this->json($this->service->normalize($stock));
    }
}
