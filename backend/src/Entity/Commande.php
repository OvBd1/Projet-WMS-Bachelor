<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\Table(name: 'commande')]
class Commande
{
    public const STATUT_EN_ATTENTE = 'EN_ATTENTE';
    public const STATUT_PREPAREE   = 'PREPAREE';
    public const STATUT_EXPEDIEE   = 'EXPEDIEE';
    public const STATUT_ANNULEE    = 'ANNULEE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCommande = null;

    #[ORM\Column(length: 20)]
    private string $statut = self::STATUT_EN_ATTENTE;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'commande', cascade: ['persist'], orphanRemoval: true)]
    private Collection $lignesCommande;

    public function __construct()
    {
        $this->lignesCommande = new ArrayCollection();
        $this->dateCommande   = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getDateCommande(): ?\DateTimeInterface { return $this->dateCommande; }
    public function setDateCommande(\DateTimeInterface $dateCommande): static { $this->dateCommande = $dateCommande; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getUtilisateur(): ?Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(?Utilisateur $utilisateur): static { $this->utilisateur = $utilisateur; return $this; }

    public function getLignesCommande(): Collection { return $this->lignesCommande; }

    public function addLigneCommande(LigneCommande $ligne): static
    {
        if (!$this->lignesCommande->contains($ligne)) {
            $this->lignesCommande->add($ligne);
            $ligne->setCommande($this);
        }
        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligne): static
    {
        if ($this->lignesCommande->removeElement($ligne) && $ligne->getCommande() === $this) {
            $ligne->setCommande(null);
        }
        return $this;
    }
}
