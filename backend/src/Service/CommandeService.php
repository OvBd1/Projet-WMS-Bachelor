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
        private EntityManagerInterface $em
    ) {}

    public function create(CommandeDTO $dto, Utilisateur $utilisateur): Commande
    {
        $commande = new Commande();
        $commande->setUtilisateur($utilisateur);

        if ($dto->tiersId) {
            $tiers = $this->tiersRepo->find($dto->tiersId);
            if (!$tiers) {
                throw new \DomainException("Tiers {$dto->tiersId} introuvable.");
            }
            $commande->setTiers($tiers);
        }

        if ($dto->dateExpedition) {
            $date = \DateTime::createFromFormat('Y-m-d', $dto->dateExpedition);
            if ($date) {
                $commande->setDateExpedition($date);
            }
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

        // Numéro provisoire pour respecter la contrainte NOT NULL/unique au premier flush,
        // puis remplacé par un numéro lisible basé sur l'id généré.
        $commande->setNumeroCommande('TMP-' . uniqid());
        $this->em->persist($commande);
        $this->em->flush();

        $commande->setNumeroCommande('CMD-' . str_pad((string)$commande->getId(), 5, '0', STR_PAD_LEFT));
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
            'id'             => $c->getId(),
            'numeroCommande' => $c->getNumeroCommande(),
            'dateCommande'   => $c->getDateCommande()?->format('Y-m-d H:i:s'),
            'dateExpedition' => $c->getDateExpedition()?->format('Y-m-d'),
            'statut'         => $c->getStatut(),
            'tiers'          => $c->getTiers() ? [
                'id'    => $c->getTiers()->getId(),
                'nom'   => $c->getTiers()->getNom(),
                'type'  => $c->getTiers()->getType(),
                'email' => $c->getTiers()->getEmail(),
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
            'id'             => $c->getId(),
            'numeroCommande' => $c->getNumeroCommande(),
            'dateCommande'   => $c->getDateCommande()?->format('Y-m-d H:i:s'),
            'dateExpedition' => $c->getDateExpedition()?->format('Y-m-d'),
            'statut'         => $c->getStatut(),
            'tiers'          => $c->getTiers() ? [
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
