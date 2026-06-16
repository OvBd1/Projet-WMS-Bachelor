<?php

namespace App\Entity;

use App\Repository\TypeEmplacementRepository;
use App\Traits\AuditTrait;
use App\Traits\DossierScopedTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeEmplacementRepository::class)]
#[ORM\Table(name: 'type_emplacement')]
#[ORM\HasLifecycleCallbacks]
class TypeEmplacement
{
    use AuditTrait, DossierScopedTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $libelle = null;

    #[ORM\OneToMany(targetEntity: Emplacement::class, mappedBy: 'typeEmplacement')]
    private Collection $emplacements;

    public function __construct()
    {
        $this->emplacements = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getLibelle(): ?string { return $this->libelle; }
    public function setLibelle(string $libelle): static { $this->libelle = $libelle; return $this; }

    public function getEmplacements(): Collection { return $this->emplacements; }
}
