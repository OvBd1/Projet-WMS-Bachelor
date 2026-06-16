<?php

namespace App\Service;

use App\DTO\DossierDTO;
use App\Entity\Dossier;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class DossierService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function create(DossierDTO $dto, ?Utilisateur $user = null): Dossier
    {
        $dossier = new Dossier();
        $this->hydrate($dossier, $dto);
        if ($user) {
            $dossier->setCreatedBy($user);
        }
        $this->em->persist($dossier);
        $this->em->flush();
        return $dossier;
    }

    public function update(Dossier $dossier, DossierDTO $dto, ?Utilisateur $user = null): Dossier
    {
        $this->hydrate($dossier, $dto);
        if ($user) {
            $dossier->setUpdatedBy($user);
        }
        $this->em->flush();
        return $dossier;
    }

    public function normalize(Dossier $d): array
    {
        return [
            'id'            => $d->getId(),
            'code'          => $d->getCode(),
            'raisonSociale' => $d->getRaisonSociale(),
            'rue'           => $d->getRue(),
            'codePostal'    => $d->getCodePostal(),
            'ville'         => $d->getVille(),
            'pays'          => $d->getPays(),
        ];
    }

    private function hydrate(Dossier $dossier, DossierDTO $dto): void
    {
        $dossier->setCode($dto->code)
                ->setRaisonSociale($dto->raisonSociale)
                ->setRue($dto->rue)
                ->setCodePostal($dto->codePostal)
                ->setVille($dto->ville)
                ->setPays($dto->pays);
    }
}
