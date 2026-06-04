<?php

namespace App\Entity;

use App\Repository\StockRepository;
use App\Traits\AuditTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockRepository::class)]
#[ORM\Table(name: 'stock')]
#[ORM\UniqueConstraint(name: 'uq_stock_article_emplacement', columns: ['article_id', 'emplacement_id'])]
#[ORM\HasLifecycleCallbacks]
class Stock
{
    use AuditTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\ManyToOne(targetEntity: Emplacement::class, inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Emplacement $emplacement = null;

    public function getId(): ?int { return $this->id; }

    public function getQuantite(): ?int { return $this->quantite; }
    public function setQuantite(int $quantite): static { $this->quantite = $quantite; return $this; }

    public function getArticle(): ?Article { return $this->article; }
    public function setArticle(?Article $article): static { $this->article = $article; return $this; }

    public function getEmplacement(): ?Emplacement { return $this->emplacement; }
    public function setEmplacement(?Emplacement $emplacement): static { $this->emplacement = $emplacement; return $this; }
}
