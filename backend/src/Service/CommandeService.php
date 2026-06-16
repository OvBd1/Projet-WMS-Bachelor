<?php

namespace App\Service;

use App\DTO\CommandeDTO;
use App\DTO\CommandeStatutDTO;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Utilisateur;
use App\Repository\ArticleRepository;
use App\Repository\TiersRepository;
use Doctrine\ORM\EntityManagerInterface;

class CommandeService
{
    public function __construct(
        private ArticleRepository $articleRepo,
        private TiersRepository $tiersRepo,
        private EntityManagerInterface $em,
        private DossierContext $dossierContext
    ) {}

    public function create(CommandeDTO $dto, Utilisateur $utilisateur): Commande
    {
        $commande = new Commande();
        $commande->setUtilisateur($utilisateur);
        $commande->setCreatedBy($utilisateur);
        $commande->setDossier($this->dossierContext->getCurrentOrThrow());

        if ($dto->tiersId) {
            $tiers = $this->tiersRepo->find($dto->tiersId);
            if (!$tiers) {
                throw new \DomainException("Tiers {$dto->tiersId} introuvable.");
            }
            $commande->setTiers($tiers);
        }

        foreach ($dto->lignes as $ligneDto) {
            $article = $this->articleRepo->find($ligneDto->articleId);
            if (!$article) {
                throw new \DomainException("Article {$ligneDto->articleId} introuvable.");
            }

            $ligne = new LigneCommande();
            $ligne->setArticle($article)->setQuantite($ligneDto->quantite);
            $commande->addLigneCommande($ligne);
        }

        $this->em->persist($commande);
        $this->em->flush();

        return $commande;
    }

    public function update(Commande $commande, CommandeDTO $dto, ?Utilisateur $user = null): Commande
    {
        if ($commande->getStatut() !== Commande::STATUT_EN_ATTENTE) {
            throw new \DomainException('Seules les commandes en attente peuvent être modifiées.');
        }

        if ($dto->dateCommande) {
            $date = \DateTime::createFromFormat('Y-m-d', $dto->dateCommande);
            if ($date) {
                $commande->setDateCommande($date);
            }
        }

        if ($dto->tiersId) {
            $tiers = $this->tiersRepo->find($dto->tiersId);
            if (!$tiers) {
                throw new \DomainException("Tiers {$dto->tiersId} introuvable.");
            }
            $commande->setTiers($tiers);
        } else {
            $commande->setTiers(null);
        }

        foreach ($commande->getLignesCommande()->toArray() as $ligne) {
            $commande->removeLigneCommande($ligne);
        }

        foreach ($dto->lignes as $ligneDto) {
            $article = $this->articleRepo->find($ligneDto->articleId);
            if (!$article) {
                throw new \DomainException("Article {$ligneDto->articleId} introuvable.");
            }

            $ligne = new LigneCommande();
            $ligne->setArticle($article)->setQuantite($ligneDto->quantite);
            $commande->addLigneCommande($ligne);
        }

        if ($user) {
            $commande->setUpdatedBy($user);
        }

        $this->em->flush();
        return $commande;
    }

    public function updateStatut(Commande $commande, CommandeStatutDTO $dto): Commande
    {
        $commande->setStatut($dto->statut);
        $this->em->flush();
        return $commande;
    }

    public function delete(Commande $commande): void
    {
        $this->em->remove($commande);
        $this->em->flush();
    }

    public function normalize(Commande $c): array
    {
        return [
            'id'           => $c->getId(),
            'dateCommande' => $c->getDateCommande()?->format('Y-m-d H:i:s'),
            'statut'       => $c->getStatut(),
            'tiers'        => $c->getTiers() ? [
                'id'   => $c->getTiers()->getId(),
                'code' => $c->getTiers()->getCode(),
                'nom'  => $c->getTiers()->getNom(),
                'type' => $c->getTiers()->getType(),
            ] : null,
            'utilisateur'  => [
                'id'    => $c->getUtilisateur()->getId(),
                'email' => $c->getUtilisateur()->getEmail(),
            ],
            'lignes' => array_map(fn($l) => [
                'id'       => $l->getId(),
                'quantite' => $l->getQuantite(),
                'article'  => [
                    'id'        => $l->getArticle()->getId(),
                    'reference' => $l->getArticle()->getReference(),
                    'libelle'   => $l->getArticle()->getLibelle(),
                ],
            ], $c->getLignesCommande()->toArray()),
        ];
    }

    public function normalizeList(Commande $c): array
    {
        return [
            'id'           => $c->getId(),
            'dateCommande' => $c->getDateCommande()?->format('Y-m-d H:i:s'),
            'statut'       => $c->getStatut(),
            'tiers'        => $c->getTiers() ? [
                'id'   => $c->getTiers()->getId(),
                'nom'  => $c->getTiers()->getNom(),
                'type' => $c->getTiers()->getType(),
            ] : null,
            'utilisateur'  => [
                'id'    => $c->getUtilisateur()->getId(),
                'email' => $c->getUtilisateur()->getEmail(),
            ],
            'nbLignes' => $c->getLignesCommande()->count(),
        ];
    }
}
