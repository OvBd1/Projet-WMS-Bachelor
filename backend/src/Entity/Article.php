<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use App\Traits\AuditTrait;
use App\Traits\DossierScopedTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name: 'article')]
#[ORM\UniqueConstraint(name: 'uq_article_dossier_reference', columns: ['dossier_id', 'reference'])]
#[ORM\HasLifecycleCallbacks]
class Article
{
    use AuditTrait, DossierScopedTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $reference = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $gestionDlc = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $gestionNumeroSerie = false;

    #[ORM\ManyToOne(targetEntity: TypeConditionnement::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: true)]
    private ?TypeConditionnement $typeConditionnement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\OneToMany(targetEntity: Stock::class, mappedBy: 'article', cascade: ['persist'], orphanRemoval: false)]
    private Collection $stocks;

    #[ORM\OneToMany(targetEntity: LigneReception::class, mappedBy: 'article')]
    private Collection $lignesReception;

    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'article')]
    private Collection $lignesCommande;

    #[ORM\OneToMany(targetEntity: TransfertEmplacement::class, mappedBy: 'article')]
    private Collection $transferts;

    public function __construct()
    {
        $this->stocks         = new ArrayCollection();
        $this->lignesReception = new ArrayCollection();
        $this->lignesCommande  = new ArrayCollection();
        $this->transferts      = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getReference(): ?string { return $this->reference; }
    public function setReference(string $reference): static { $this->reference = $reference; return $this; }

    public function getLibelle(): ?string { return $this->libelle; }
    public function setLibelle(string $libelle): static { $this->libelle = $libelle; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function isGestionDlc(): bool { return $this->gestionDlc; }
    public function setGestionDlc(bool $gestionDlc): static { $this->gestionDlc = $gestionDlc; return $this; }

    public function isGestionNumeroSerie(): bool { return $this->gestionNumeroSerie; }
    public function setGestionNumeroSerie(bool $gestionNumeroSerie): static { $this->gestionNumeroSerie = $gestionNumeroSerie; return $this; }

    public function getTypeConditionnement(): ?TypeConditionnement { return $this->typeConditionnement; }
    public function setTypeConditionnement(?TypeConditionnement $typeConditionnement): static { $this->typeConditionnement = $typeConditionnement; return $this; }

    public function getImagePath(): ?string { return $this->imagePath; }
    public function setImagePath(?string $imagePath): static { $this->imagePath = $imagePath; return $this; }

    public function getStocks(): Collection { return $this->stocks; }
    public function getLignesReception(): Collection { return $this->lignesReception; }
    public function getLignesCommande(): Collection { return $this->lignesCommande; }
    public function getTransferts(): Collection { return $this->transferts; }
}
