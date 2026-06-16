<?php

namespace App\Entity;

use App\Repository\EmplacementRepository;
use App\Traits\AuditTrait;
use App\Traits\DossierScopedTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmplacementRepository::class)]
#[ORM\Table(name: 'emplacement')]
#[ORM\UniqueConstraint(name: 'uq_emplacement_dossier_code', columns: ['dossier_id', 'code'])]
#[ORM\HasLifecycleCallbacks]
class Emplacement
{
    use AuditTrait, DossierScopedTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $code = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: TypeEmplacement::class, inversedBy: 'emplacements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeEmplacement $typeEmplacement = null;

    #[ORM\OneToMany(targetEntity: Stock::class, mappedBy: 'emplacement')]
    private Collection $stocks;

    #[ORM\OneToMany(targetEntity: LigneReception::class, mappedBy: 'emplacement')]
    private Collection $lignesReception;

    #[ORM\OneToMany(targetEntity: TransfertEmplacement::class, mappedBy: 'emplacementSource')]
    private Collection $transfertsSource;

    #[ORM\OneToMany(targetEntity: TransfertEmplacement::class, mappedBy: 'emplacementDestination')]
    private Collection $transfertsDestination;

    public function __construct()
    {
        $this->stocks                = new ArrayCollection();
        $this->lignesReception       = new ArrayCollection();
        $this->transfertsSource      = new ArrayCollection();
        $this->transfertsDestination = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = $code; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getTypeEmplacement(): ?TypeEmplacement { return $this->typeEmplacement; }
    public function setTypeEmplacement(?TypeEmplacement $typeEmplacement): static { $this->typeEmplacement = $typeEmplacement; return $this; }

    public function getStocks(): Collection { return $this->stocks; }
    public function getLignesReception(): Collection { return $this->lignesReception; }
    public function getTransfertsSource(): Collection { return $this->transfertsSource; }
    public function getTransfertsDestination(): Collection { return $this->transfertsDestination; }
}
