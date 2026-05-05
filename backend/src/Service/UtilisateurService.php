<?php

namespace App\Service;

use App\DTO\RegisterDTO;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurService
{
    public function __construct(
        private UtilisateurRepository $repo,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {}

    public function register(RegisterDTO $dto): Utilisateur
    {
        if ($this->repo->findOneBy(['email' => $dto->email])) {
            throw new \DomainException('Cet email est déjà utilisé.');
        }

        $user = new Utilisateur();
        $user->setEmail($dto->email)
             ->setRole($dto->role)
             ->setPassword($this->hasher->hashPassword($user, $dto->password));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function normalize(Utilisateur $u): array
    {
        return [
            'id'    => $u->getId(),
            'email' => $u->getEmail(),
            'role'  => $u->getRole(),
        ];
    }
}
