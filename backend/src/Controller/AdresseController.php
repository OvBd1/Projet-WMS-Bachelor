<?php

namespace App\Controller;

use App\Service\AdresseApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Passerelle vers la Base Adresse Nationale, derrière le pare-feu JWT (^/api).
 * Le navigateur n'appelle jamais le service tiers directement.
 */
#[Route('/api/adresses', name: 'api_adresses_')]
class AdresseController extends AbstractController
{
    public function __construct(private AdresseApiService $service) {}

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = (string) $request->query->get('q', '');
        $limit = $request->query->getInt('limit', 5);

        return $this->json($this->service->search($query, $limit));
    }
}
