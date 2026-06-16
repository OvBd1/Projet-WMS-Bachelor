<?php

namespace App\Service;

use App\DTO\TransfertDTO;
use App\Entity\TransfertEmplacement;
use App\Entity\Utilisateur;
use App\Repository\ArticleRepository;
use App\Repository\EmplacementRepository;
use Doctrine\ORM\EntityManagerInterface;

class TransfertService
{
    public function __construct(
        private ArticleRepository $articleRepo,
        private EmplacementRepository $emplacementRepo,
        private StockService $stockService,
        private EntityManagerInterface $em,
        private DossierContext $dossierContext
    ) {}

    public function create(TransfertDTO $dto, Utilisateur $utilisateur): TransfertEmplacement
    {
        $article = $this->articleRepo->find($dto->articleId);
        if (!$article) {
            throw new \DomainException("Article introuvable.");
        }

        $source = $this->emplacementRepo->find($dto->emplacementSourceId);
        if (!$source) {
            throw new \DomainException("Emplacement source introuvable.");
        }

        $destination = $this->emplacementRepo->find($dto->emplacementDestinationId);
        if (!$destination) {
            throw new \DomainException("Emplacement destination introuvable.");
        }

        if ($source->getId() === $destination->getId()) {
            throw new \DomainException("L'emplacement source et destination doivent être différents.");
        }

        $this->stockService->adjust($article, $source, -$dto->quantite);
        $this->stockService->adjust($article, $destination, $dto->quantite);

        $transfert = new TransfertEmplacement();
        $transfert->setUtilisateur($utilisateur)
                  ->setArticle($article)
                  ->setEmplacementSource($source)
                  ->setEmplacementDestination($destination)
                  ->setQuantite($dto->quantite)
                  ->setDossier($this->dossierContext->getCurrentOrThrow());

        $this->em->persist($transfert);
        $this->em->flush();

        return $transfert;
    }

    public function normalize(TransfertEmplacement $t): array
    {
        return [
            'id'             => $t->getId(),
            'dateTransfert'  => $t->getDateTransfert()?->format('Y-m-d H:i:s'),
            'quantite'       => $t->getQuantite(),
            'utilisateur'    => [
                'id'    => $t->getUtilisateur()->getId(),
                'email' => $t->getUtilisateur()->getEmail(),
            ],
            'article'        => [
                'id'        => $t->getArticle()->getId(),
                'reference' => $t->getArticle()->getReference(),
                'libelle'   => $t->getArticle()->getLibelle(),
            ],
            'emplacementSource'      => [
                'id'   => $t->getEmplacementSource()->getId(),
                'code' => $t->getEmplacementSource()->getCode(),
            ],
            'emplacementDestination' => [
                'id'   => $t->getEmplacementDestination()->getId(),
                'code' => $t->getEmplacementDestination()->getCode(),
            ],
        ];
    }
}
