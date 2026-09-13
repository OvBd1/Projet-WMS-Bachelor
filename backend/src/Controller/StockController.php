<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\EmplacementRepository;
use App\Repository\StockRepository;
use App\Service\StockService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Consultation des stocks, en lecture seule.
 *
 * Aucune route n'écrit de quantité : le stock ne varie que par les mouvements
 * (réceptions, transferts, commandes) via StockService::adjust().
 */
#[Route('/api/stocks', name: 'api_stocks_')]
class StockController extends AbstractController
{
    public function __construct(
        private StockService $service,
        private StockRepository $repo,
        private ArticleRepository $articleRepo,
        private EmplacementRepository $emplacementRepo
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
}
