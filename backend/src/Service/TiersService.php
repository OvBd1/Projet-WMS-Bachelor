<?php

namespace App\Service;

use App\DTO\TiersDTO;
use App\Entity\Tiers;
use Doctrine\ORM\EntityManagerInterface;

class TiersService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function create(TiersDTO $dto): Tiers
    {
        $tiers = new Tiers();
        $this->hydrate($tiers, $dto);
        $this->em->persist($tiers);
        $this->em->flush();
        return $tiers;
    }

    public function update(Tiers $tiers, TiersDTO $dto): Tiers
    {
        $this->hydrate($tiers, $dto);
        $this->em->flush();
        return $tiers;
    }

    public function delete(Tiers $tiers): void
    {
        $this->em->remove($tiers);
        $this->em->flush();
    }

    public function normalize(Tiers $t): array
    {
        return [
            'id'        => $t->getId(),
            'nom'       => $t->getNom(),
            'type'      => $t->getType(),
            'email'     => $t->getEmail(),
            'telephone' => $t->getTelephone(),
            'adresse'   => $t->getAdresse(),
        ];
    }

    private function hydrate(Tiers $tiers, TiersDTO $dto): void
    {
        $tiers->setNom($dto->nom)
              ->setType($dto->type)
              ->setEmail($dto->email)
              ->setTelephone($dto->telephone)
              ->setAdresse($dto->adresse);
    }
}
