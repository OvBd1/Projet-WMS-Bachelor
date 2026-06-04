<?php

namespace App\Entity;

use App\Repository\TypeConditionnementRepository;
use App\Traits\AuditTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeConditionnementRepository::class)]
#[ORM\Table(name: 'type_conditionnement')]
#[ORM\HasLifecycleCallbacks]
class TypeConditionnement
{
    use AuditTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $libelle = null;

    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'typeConditionnement')]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getLibelle(): ?string { return $this->libelle; }
    public function setLibelle(string $libelle): static { $this->libelle = $libelle; return $this; }

    public function getArticles(): Collection { return $this->articles; }
}
