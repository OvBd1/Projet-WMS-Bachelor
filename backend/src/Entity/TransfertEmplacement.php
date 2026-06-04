<?php

namespace App\Entity;

use App\Repository\TransfertEmplacementRepository;
use App\Traits\AuditTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransfertEmplacementRepository::class)]
#[ORM\Table(name: 'transfert_emplacement')]
#[ORM\HasLifecycleCallbacks]
class TransfertEmplacement
{
    use AuditTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateTransfert = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'transferts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'transferts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\ManyToOne(targetEntity: Emplacement::class, inversedBy: 'transfertsSource')]
    #[ORM\JoinColumn(name: 'emplacement_source_id', nullable: false)]
    private ?Emplacement $emplacementSource = null;

    #[ORM\ManyToOne(targetEntity: Emplacement::class, inversedBy: 'transfertsDestination')]
    #[ORM\JoinColumn(name: 'emplacement_destination_id', nullable: false)]
    private ?Emplacement $emplacementDestination = null;

    public function __construct()
    {
        $this->dateTransfert = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getDateTransfert(): ?\DateTimeInterface { return $this->dateTransfert; }
    public function setDateTransfert(\DateTimeInterface $dateTransfert): static { $this->dateTransfert = $dateTransfert; return $this; }

    public function getQuantite(): ?int { return $this->quantite; }
    public function setQuantite(int $quantite): static { $this->quantite = $quantite; return $this; }

    public function getUtilisateur(): ?Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(?Utilisateur $utilisateur): static { $this->utilisateur = $utilisateur; return $this; }

    public function getArticle(): ?Article { return $this->article; }
    public function setArticle(?Article $article): static { $this->article = $article; return $this; }

    public function getEmplacementSource(): ?Emplacement { return $this->emplacementSource; }
    public function setEmplacementSource(?Emplacement $emplacement): static { $this->emplacementSource = $emplacement; return $this; }

    public function getEmplacementDestination(): ?Emplacement { return $this->emplacementDestination; }
    public function setEmplacementDestination(?Emplacement $emplacement): static { $this->emplacementDestination = $emplacement; return $this; }
}
