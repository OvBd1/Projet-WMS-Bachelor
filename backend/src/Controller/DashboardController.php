<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commande;
use App\Entity\Emplacement;
use App\Entity\Reception;
use App\Entity\Stock;
use App\Entity\TransfertEmplacement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard', name: 'api_dashboard_')]
class DashboardController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        return $this->json([
            'articles'     => $this->em->getRepository(Article::class)->count([]),
            'emplacements' => $this->em->getRepository(Emplacement::class)->count([]),
            'stocks'       => $this->em->getRepository(Stock::class)->count([]),
            'receptions'   => $this->em->getRepository(Reception::class)->count([]),
            'commandes'    => $this->em->getRepository(Commande::class)->count([]),
            'transferts'   => $this->em->getRepository(TransfertEmplacement::class)->count([]),
        ]);
    }
}
