<?php

namespace App\Entity;

use App\Repository\ReceptionRepository;
use App\Traits\AuditTrait;
use App\Traits\DossierScopedTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReceptionRepository::class)]
#[ORM\Table(name: 'reception')]
#[ORM\HasLifecycleCallbacks]
class Reception
{
    use AuditTrait, DossierScopedTrait;

    public const STATUT_EN_ATTENTE = 'EN_ATTENTE';
    public const STATUT_VALIDEE    = 'VALIDEE';
    public const STATUT_ANNULEE    = 'ANNULEE';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateReception = null;

    #[ORM\Column(length: 20)]
    private string $statut = 'EN_ATTENTE';

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'receptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $validatedAt = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Utilisateur $validatedBy = null;

    #[ORM\ManyToOne(targetEntity: Tiers::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Tiers $tiers = null;

    #[ORM\OneToMany(targetEntity: LigneReception::class, mappedBy: 'reception', cascade: ['persist'], orphanRemoval: true)]
    private Collection $lignesReception;

    public function __construct()
    {
        $this->lignesReception = new ArrayCollection();
        $this->dateReception   = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getDateReception(): ?\DateTimeInterface { return $this->dateReception; }
    public function setDateReception(\DateTimeInterface $dateReception): static { $this->dateReception = $dateReception; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getUtilisateur(): ?Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(?Utilisateur $utilisateur): static { $this->utilisateur = $utilisateur; return $this; }

    public function getValidatedAt(): ?\DateTimeInterface { return $this->validatedAt; }
    public function setValidatedAt(?\DateTimeInterface $validatedAt): static { $this->validatedAt = $validatedAt; return $this; }

    public function getValidatedBy(): ?Utilisateur { return $this->validatedBy; }
    public function setValidatedBy(?Utilisateur $validatedBy): static { $this->validatedBy = $validatedBy; return $this; }

    public function getTiers(): ?Tiers { return $this->tiers; }
    public function setTiers(?Tiers $tiers): static { $this->tiers = $tiers; return $this; }

    public function getLignesReception(): Collection { return $this->lignesReception; }

    public function addLigneReception(LigneReception $ligne): static
    {
        if (!$this->lignesReception->contains($ligne)) {
            $this->lignesReception->add($ligne);
            $ligne->setReception($this);
        }
        return $this;
    }

    public function removeLigneReception(LigneReception $ligne): static
    {
        if ($this->lignesReception->removeElement($ligne) && $ligne->getReception() === $this) {
            $ligne->setReception(null);
        }
        return $this;
    }
}
