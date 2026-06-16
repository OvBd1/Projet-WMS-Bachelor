<?php

namespace App\Service;

use App\DTO\TypeEmplacementDTO;
use App\Entity\TypeEmplacement;
use App\Entity\Utilisateur;
use App\Repository\TypeEmplacementRepository;
use Doctrine\ORM\EntityManagerInterface;

class TypeEmplacementService
{
    public function __construct(
        private TypeEmplacementRepository $repo,
        private EntityManagerInterface $em,
        private DossierContext $dossierContext
    ) {}

    public function create(TypeEmplacementDTO $dto, ?Utilisateur $user = null): TypeEmplacement
    {
        $type = new TypeEmplacement();
        $type->setLibelle($dto->libelle);
        $type->setDossier($this->dossierContext->getCurrentOrThrow());
        if ($user) {
            $type->setCreatedBy($user);
        }
        $this->em->persist($type);
        $this->em->flush();
        return $type;
    }

    public function update(TypeEmplacement $type, TypeEmplacementDTO $dto, ?Utilisateur $user = null): TypeEmplacement
    {
        $type->setLibelle($dto->libelle);
        if ($user) {
            $type->setUpdatedBy($user);
        }
        $this->em->flush();
        return $type;
    }

    public function delete(TypeEmplacement $type): void
    {
        $this->em->remove($type);
        $this->em->flush();
    }

    public function normalize(TypeEmplacement $t): array
    {
        return [
            'id'      => $t->getId(),
            'libelle' => $t->getLibelle(),
        ];
    }
}
