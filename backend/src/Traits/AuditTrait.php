<?php

namespace App\Traits;

use App\Entity\Utilisateur;
use Doctrine\ORM\Mapping as ORM;

trait AuditTrait
{
    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Utilisateur $createdBy = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Utilisateur $updatedBy = null;

    #[ORM\PrePersist]
    public function initAudit(): void
    {
        $this->createdAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function touchAudit(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt ?? null; }
    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }

    public function getCreatedBy(): ?Utilisateur { return $this->createdBy; }
    public function setCreatedBy(?Utilisateur $createdBy): static { $this->createdBy = $createdBy; return $this; }

    public function getUpdatedBy(): ?Utilisateur { return $this->updatedBy; }
    public function setUpdatedBy(?Utilisateur $updatedBy): static { $this->updatedBy = $updatedBy; return $this; }
}
