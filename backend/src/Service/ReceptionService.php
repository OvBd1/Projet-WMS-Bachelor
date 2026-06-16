<?php

namespace App\Service;

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
        private EntityManagerInterface $em,
        private DossierContext $dossierContext
    ) {}

    public function create(ReceptionDTO $dto, Utilisateur $utilisateur): Reception
    {
        $reception = new Reception();
        $reception->setUtilisateur($utilisateur);
        $reception->setStatut(Reception::STATUT_EN_ATTENTE);
        $reception->setCreatedBy($utilisateur);
        $reception->setDossier($this->dossierContext->getCurrentOrThrow());

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
            $reception->addLigneReception($this->buildLigne($ligneDto));
        }

        $this->em->persist($reception);
        $this->em->flush();

        return $reception;
    }

    public function update(Reception $reception, ReceptionDTO $dto, ?Utilisateur $user = null): Reception
    {
        if ($reception->getStatut() !== Reception::STATUT_EN_ATTENTE) {
            throw new \DomainException('Seules les réceptions en attente peuvent être modifiées.');
        }

        if ($user) {
            $reception->setUpdatedBy($user);
        }

        if ($dto->tiersId) {
            $tiers = $this->tiersRepo->find($dto->tiersId);
            if (!$tiers) {
                throw new \DomainException("Tiers {$dto->tiersId} introuvable.");
            }
            $reception->setTiers($tiers);
        } else {
            $reception->setTiers(null);
        }

        if ($dto->dateReception) {
            $date = \DateTime::createFromFormat('Y-m-d', $dto->dateReception);
            if ($date) {
                $reception->setDateReception($date);
            }
        }

        foreach ($reception->getLignesReception()->toArray() as $ligne) {
            $reception->removeLigneReception($ligne);
        }

        foreach ($dto->lignes as $ligneDto) {
            $reception->addLigneReception($this->buildLigne($ligneDto));
        }

        $this->em->flush();

        return $reception;
    }

    public function valider(Reception $reception, Utilisateur $user): Reception
    {
        if ($reception->getStatut() !== Reception::STATUT_EN_ATTENTE) {
            throw new \DomainException('Seules les réceptions en attente peuvent être validées.');
        }

        foreach ($reception->getLignesReception() as $ligne) {
            $this->stockService->adjust($ligne->getArticle(), $ligne->getEmplacement(), $ligne->getQuantite());
        }

        $reception->setStatut(Reception::STATUT_VALIDEE);
        $reception->setValidatedAt(new \DateTime());
        $reception->setValidatedBy($user);
        $reception->setUpdatedBy($user);

        $this->em->flush();

        return $reception;
    }

    public function annuler(Reception $reception, ?Utilisateur $user = null): Reception
    {
        if ($reception->getStatut() === Reception::STATUT_ANNULEE) {
            throw new \DomainException('Cette réception est déjà annulée.');
        }

        if ($reception->getStatut() === Reception::STATUT_VALIDEE) {
            foreach ($reception->getLignesReception() as $ligne) {
                $this->stockService->adjust($ligne->getArticle(), $ligne->getEmplacement(), -$ligne->getQuantite());
            }
        }

        $reception->setStatut(Reception::STATUT_ANNULEE);
        if ($user) {
            $reception->setUpdatedBy($user);
        }

        $this->em->flush();

        return $reception;
    }

    public function delete(Reception $reception): void
    {
        if ($reception->getStatut() === Reception::STATUT_VALIDEE) {
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
            'validatedAt'   => $r->getValidatedAt()?->format('Y-m-d H:i:s'),
            'validatedBy'   => $r->getValidatedBy() ? [
                'id'    => $r->getValidatedBy()->getId(),
                'email' => $r->getValidatedBy()->getEmail(),
            ] : null,
            'createdAt'     => $r->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt'     => $r->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'createdBy'     => $r->getCreatedBy() ? [
                'id'    => $r->getCreatedBy()->getId(),
                'email' => $r->getCreatedBy()->getEmail(),
            ] : null,
            'updatedBy'     => $r->getUpdatedBy() ? [
                'id'    => $r->getUpdatedBy()->getId(),
                'email' => $r->getUpdatedBy()->getEmail(),
            ] : null,
            'tiers'         => $r->getTiers() ? [
                'id'   => $r->getTiers()->getId(),
                'code' => $r->getTiers()->getCode(),
                'nom'  => $r->getTiers()->getNom(),
                'type' => $r->getTiers()->getType(),
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
            'validatedAt'   => $r->getValidatedAt()?->format('Y-m-d H:i:s'),
            'validatedBy'   => $r->getValidatedBy() ? [
                'id'    => $r->getValidatedBy()->getId(),
                'email' => $r->getValidatedBy()->getEmail(),
            ] : null,
            'createdAt'     => $r->getCreatedAt()?->format('Y-m-d H:i:s'),
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

    private function buildLigne(object $ligneDto): LigneReception
    {
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

        return $ligne;
    }
}
