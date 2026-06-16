<?php

namespace App\Service;

use App\DTO\RegisterDTO;
use App\Entity\Utilisateur;
use App\Repository\DossierRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurService
{
    public function __construct(
        private UtilisateurRepository $repo,
        private DossierRepository $dossierRepo,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {}

    public function register(RegisterDTO $dto): Utilisateur
    {
        if ($this->repo->findOneBy(['email' => $dto->email])) {
            throw new \DomainException('Cet email est déjà utilisé.');
        }

        $dossier = null;
        if ($dto->role === 'ROLE_USER') {
            if (!$dto->dossierId) {
                throw new \DomainException('Un dossier est requis pour un utilisateur non-admin.');
            }
            $dossier = $this->dossierRepo->find($dto->dossierId);
            if (!$dossier) {
                throw new \DomainException("Dossier {$dto->dossierId} introuvable.");
            }
        }

        $user = new Utilisateur();
        $user->setEmail($dto->email)
             ->setNom($dto->nom)
             ->setPrenom($dto->prenom)
             ->setRole($dto->role)
             ->setDossier($dossier)
             ->setPassword($this->hasher->hashPassword($user, $dto->password));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function normalize(Utilisateur $u): array
    {
        return [
            'id'      => $u->getId(),
            'email'   => $u->getEmail(),
            'nom'     => $u->getNom(),
            'prenom'  => $u->getPrenom(),
            'role'    => $u->getRole(),
            'dossier' => $u->getDossier() ? [
                'id'            => $u->getDossier()->getId(),
                'code'          => $u->getDossier()->getCode(),
                'raisonSociale' => $u->getDossier()->getRaisonSociale(),
            ] : null,
        ];
    }
}
