<?php

namespace App\Service;

use App\DTO\TypeEmplacementDTO;
use App\Entity\TypeEmplacement;
use App\Repository\TypeEmplacementRepository;
use Doctrine\ORM\EntityManagerInterface;

class TypeEmplacementService
{
    public function __construct(
        private TypeEmplacementRepository $repo,
        private EntityManagerInterface $em
    ) {}

    public function create(TypeEmplacementDTO $dto): TypeEmplacement
    {
        $type = new TypeEmplacement();
        $type->setLibelle($dto->libelle);
        $this->em->persist($type);
        $this->em->flush();
        return $type;
    }

    public function update(TypeEmplacement $type, TypeEmplacementDTO $dto): TypeEmplacement
    {
        $type->setLibelle($dto->libelle);
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
