<?php

namespace App\Service;

use App\DTO\TypeConditionnementDTO;
use App\Entity\TypeConditionnement;
use App\Repository\TypeConditionnementRepository;
use Doctrine\ORM\EntityManagerInterface;

class TypeConditionnementService
{
    public function __construct(
        private TypeConditionnementRepository $repo,
        private EntityManagerInterface $em
    ) {}

    public function create(TypeConditionnementDTO $dto): TypeConditionnement
    {
        $type = new TypeConditionnement();
        $type->setLibelle($dto->libelle);
        $this->em->persist($type);
        $this->em->flush();
        return $type;
    }

    public function update(TypeConditionnement $type, TypeConditionnementDTO $dto): TypeConditionnement
    {
        $type->setLibelle($dto->libelle);
        $this->em->flush();
        return $type;
    }

    public function delete(TypeConditionnement $type): void
    {
        $this->em->remove($type);
        $this->em->flush();
    }

    public function normalize(TypeConditionnement $t): array
    {
        return [
            'id'      => $t->getId(),
            'libelle' => $t->getLibelle(),
        ];
    }
}
