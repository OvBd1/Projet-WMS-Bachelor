<?php

namespace App\Service;

use App\DTO\LigneReceptionUpdateDTO;
use App\DTO\ReceptionDTO;
use App\Entity\LigneReception;
use App\Entity\Reception;
use App\Entity\Utilisateur;
use App\Repository\ArticleRepository;
use App\Repository\EmplacementRepository;
use App\Repository\TiersRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReceptionService
{
    public function __construct(
        private ArticleRepository $articleRepo,
        private EmplacementRepository $emplacementRepo,
        private TiersRepository $tiersRepo,
        private StockService $stockService,
        private EntityManagerInterface $em
    ) {}

    public function create(ReceptionDTO $dto, Utilisateur $utilisateur): Reception
    {
        $reception = new Reception();
        $reception->setUtilisateur($utilisateur);
        $reception->setStatut('EN_ATTENTE');

        if ($dto->dateReception) {
            $date = \DateTime::createFromFormat('Y-m-d', $dto->dateReception);
            if ($date) {
                $reception->setDateReception($date);
            }
        }

        if ($dto->tiersId) {
            $tiers = $this->tiersRepo->find($dto->tiersId);
            if (!$tiers) {
                throw new \DomainException("Tiers {$dto->tiersId} introuvable.");
            }
            $reception->setTiers($tiers);
        }

        foreach ($dto->lignes as $ligneDto) {
            $article = $this->articleRepo->find($ligneDto->articleId);
            if (!$article) {
                throw new \DomainException("Article {$ligneDto->articleId} introuvable.");
            }

            $emplacement = $this->emplacementRepo->find($ligneDto->emplacementId);
            if (!$emplacement) {
                throw new \DomainException("Emplacement {$ligneDto->emplacementId} introuvable.");
            }

            if ($article->isGestionDlc() && !$ligneDto->dlc) {
                throw new \DomainException("La DLC est obligatoire pour l'article {$article->getReference()}.");
            }

            if ($article->isGestionNumeroSerie() && !$ligneDto->numeroSerie) {
                throw new \DomainException("Le numéro de série est obligatoire pour l'article {$article->getReference()}.");
            }

            $ligne = new LigneReception();
            $ligne->setArticle($article)
                  ->setEmplacement($emplacement)
                  ->setQuantite($ligneDto->quantite);

            if ($ligneDto->dlc) {
                $dlcDate = \DateTime::createFromFormat('Y-m-d', $ligneDto->dlc);
                if ($dlcDate) {
                    $ligne->setDlc($dlcDate);
                }
            }

            $ligne->setNumeroSerie($ligneDto->numeroSerie);

            $reception->addLigneReception($ligne);
        }

        $this->em->persist($reception);
        $this->em->flush();

        return $reception;
    }

    public function updateLigne(Reception $reception, LigneReception $ligne, LigneReceptionUpdateDTO $dto): LigneReception
    {
        if ($reception->getStatut() !== 'EN_ATTENTE') {
            throw new \DomainException("Seules les lignes d'une réception en attente peuvent être modifiées.");
        }

        $emplacement = $this->emplacementRepo->find($dto->emplacementId);
        if (!$emplacement) {
            throw new \DomainException("Emplacement {$dto->emplacementId} introuvable.");
        }

        $article = $ligne->getArticle();

        if ($article->isGestionDlc() && !$dto->dlc) {
            throw new \DomainException("La DLC est obligatoire pour l'article {$article->getReference()}.");
        }

        if ($article->isGestionNumeroSerie() && !$dto->numeroSerie) {
            throw new \DomainException("Le numéro de série est obligatoire pour l'article {$article->getReference()}.");
        }

        $ligne->setEmplacement($emplacement)
              ->setQuantite($dto->quantite);

        if ($dto->dlc) {
            $dlcDate = \DateTime::createFromFormat('Y-m-d', $dto->dlc);
            $ligne->setDlc($dlcDate ?: null);
        } else {
            $ligne->setDlc(null);
        }

        $ligne->setNumeroSerie($dto->numeroSerie);

        $this->em->flush();

        return $ligne;
    }

    public function valider(Reception $reception): Reception
    {
        if ($reception->getStatut() !== 'EN_ATTENTE') {
            throw new \DomainException('Seules les réceptions en attente peuvent être validées.');
        }

        foreach ($reception->getLignesReception() as $ligne) {
            $this->stockService->adjust($ligne->getArticle(), $ligne->getEmplacement(), $ligne->getQuantite());
        }

        $reception->setStatut('VALIDEE');
        $this->em->flush();

        return $reception;
    }

    public function annuler(Reception $reception): Reception
    {
        if ($reception->getStatut() === 'VALIDEE') {
            throw new \DomainException('Une réception validée ne peut pas être annulée.');
        }

        $reception->setStatut('ANNULEE');
        $this->em->flush();

        return $reception;
    }

    public function delete(Reception $reception): void
    {
        if ($reception->getStatut() === 'VALIDEE') {
            throw new \DomainException('Une réception validée ne peut pas être supprimée.');
        }

        $this->em->remove($reception);
        $this->em->flush();
    }

    public function normalize(Reception $r): array
    {
        return [
            'id'            => $r->getId(),
            'dateReception' => $r->getDateReception()?->format('Y-m-d H:i:s'),
            'statut'        => $r->getStatut(),
            'tiers'         => $r->getTiers() ? [
                'id'        => $r->getTiers()->getId(),
                'nom'       => $r->getTiers()->getNom(),
                'type'      => $r->getTiers()->getType(),
                'email'     => $r->getTiers()->getEmail(),
                'telephone' => $r->getTiers()->getTelephone(),
            ] : null,
            'utilisateur'   => [
                'id'    => $r->getUtilisateur()->getId(),
                'email' => $r->getUtilisateur()->getEmail(),
            ],
            'lignes' => array_map(fn($l) => [
                'id'          => $l->getId(),
                'quantite'    => $l->getQuantite(),
                'dlc'         => $l->getDlc()?->format('Y-m-d'),
                'numeroSerie' => $l->getNumeroSerie(),
                'article'     => [
                    'id'                 => $l->getArticle()->getId(),
                    'reference'          => $l->getArticle()->getReference(),
                    'libelle'            => $l->getArticle()->getLibelle(),
                    'gestionDlc'         => $l->getArticle()->isGestionDlc(),
                    'gestionNumeroSerie' => $l->getArticle()->isGestionNumeroSerie(),
                ],
                'emplacement' => [
                    'id'   => $l->getEmplacement()->getId(),
                    'code' => $l->getEmplacement()->getCode(),
                ],
            ], $r->getLignesReception()->toArray()),
        ];
    }

    public function normalizeList(Reception $r): array
    {
        return [
            'id'            => $r->getId(),
            'dateReception' => $r->getDateReception()?->format('Y-m-d H:i:s'),
            'statut'        => $r->getStatut(),
            'tiers'         => $r->getTiers() ? [
                'id'   => $r->getTiers()->getId(),
                'nom'  => $r->getTiers()->getNom(),
                'type' => $r->getTiers()->getType(),
            ] : null,
            'utilisateur'   => [
                'id'    => $r->getUtilisateur()->getId(),
                'email' => $r->getUtilisateur()->getEmail(),
            ],
            'nbLignes' => $r->getLignesReception()->count(),
        ];
    }
}
