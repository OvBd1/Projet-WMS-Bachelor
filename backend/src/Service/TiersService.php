<?php

namespace App\Service;

use App\DTO\TiersDTO;
use App\Entity\Tiers;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class TiersService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function create(TiersDTO $dto, ?Utilisateur $user = null): Tiers
    {
        $tiers = new Tiers();
        $this->hydrate($tiers, $dto);
        if ($user) {
            $tiers->setCreatedBy($user);
        }
        $this->em->persist($tiers);
        $this->em->flush();
        return $tiers;
    }

    public function update(Tiers $tiers, TiersDTO $dto, ?Utilisateur $user = null): Tiers
    {
        $this->hydrate($tiers, $dto);
        if ($user) {
            $tiers->setUpdatedBy($user);
        }
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
            'id'         => $t->getId(),
            'code'       => $t->getCode(),
            'nom'        => $t->getNom(),
            'type'       => $t->getType(),
            'rue'        => $t->getRue(),
            'codePostal' => $t->getCodePostal(),
            'ville'      => $t->getVille(),
            'pays'       => $t->getPays(),
        ];
    }

    private function hydrate(Tiers $tiers, TiersDTO $dto): void
    {
        $tiers->setCode($dto->code)
              ->setNom($dto->nom)
              ->setType($dto->type)
              ->setRue($dto->rue)
              ->setCodePostal($dto->codePostal)
              ->setVille($dto->ville)
              ->setPays($dto->pays);
    }
}
