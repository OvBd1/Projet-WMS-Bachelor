<?php

namespace App\Entity;

use App\Repository\LigneReceptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneReceptionRepository::class)]
#[ORM\Table(name: 'ligne_reception')]
class LigneReception
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dlc = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $numeroSerie = null;

    #[ORM\ManyToOne(targetEntity: Reception::class, inversedBy: 'lignesReception')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reception $reception = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'lignesReception')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\ManyToOne(targetEntity: Emplacement::class, inversedBy: 'lignesReception')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Emplacement $emplacement = null;

    public function getId(): ?int { return $this->id; }

    public function getQuantite(): ?int { return $this->quantite; }
    public function setQuantite(int $quantite): static { $this->quantite = $quantite; return $this; }

    public function getDlc(): ?\DateTimeInterface { return $this->dlc; }
    public function setDlc(?\DateTimeInterface $dlc): static { $this->dlc = $dlc; return $this; }

    public function getNumeroSerie(): ?string { return $this->numeroSerie; }
    public function setNumeroSerie(?string $numeroSerie): static { $this->numeroSerie = $numeroSerie; return $this; }

    public function getReception(): ?Reception { return $this->reception; }
    public function setReception(?Reception $reception): static { $this->reception = $reception; return $this; }

    public function getArticle(): ?Article { return $this->article; }
    public function setArticle(?Article $article): static { $this->article = $article; return $this; }

    public function getEmplacement(): ?Emplacement { return $this->emplacement; }
    public function setEmplacement(?Emplacement $emplacement): static { $this->emplacement = $emplacement; return $this; }
}
