<?php

namespace App\Traits;

use App\Entity\Dossier;
use Doctrine\ORM\Mapping as ORM;

trait DossierScopedTrait
{
    #[ORM\ManyToOne(targetEntity: Dossier::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Dossier $dossier = null;

    public function getDossier(): ?Dossier { return $this->dossier; }
    public function setDossier(Dossier $dossier): static { $this->dossier = $dossier; return $this; }
}
