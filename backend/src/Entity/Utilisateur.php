<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use App\Traits\AuditTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[ORM\HasLifecycleCallbacks]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    use AuditTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private string $role = 'ROLE_USER';

    #[ORM\OneToMany(targetEntity: Reception::class, mappedBy: 'utilisateur')]
    private Collection $receptions;

    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'utilisateur')]
    private Collection $commandes;

    #[ORM\OneToMany(targetEntity: TransfertEmplacement::class, mappedBy: 'utilisateur')]
    private Collection $transferts;

    public function __construct()
    {
        $this->receptions = new ArrayCollection();
        $this->commandes  = new ArrayCollection();
        $this->transferts = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getUserIdentifier(): string { return (string) $this->email; }

    public function getRoles(): array { return [$this->role]; }

    public function getRole(): string { return $this->role; }
    public function setRole(string $role): static { $this->role = $role; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function eraseCredentials(): void {}

    public function getReceptions(): Collection { return $this->receptions; }
    public function getCommandes(): Collection { return $this->commandes; }
    public function getTransferts(): Collection { return $this->transferts; }
}
