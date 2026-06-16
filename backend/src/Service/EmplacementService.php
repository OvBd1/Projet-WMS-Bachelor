<?php

namespace App\Service;

use App\DTO\EmplacementDTO;
use App\Entity\Emplacement;
use App\Entity\Utilisateur;
use App\Repository\EmplacementRepository;
use App\Repository\TypeEmplacementRepository;
use Doctrine\ORM\EntityManagerInterface;

class EmplacementService
{
    public function __construct(
        private EmplacementRepository $repo,
        private TypeEmplacementRepository $typeRepo,
        private EntityManagerInterface $em,
        private DossierContext $dossierContext
    ) {}

    public function create(EmplacementDTO $dto, ?Utilisateur $user = null): Emplacement
    {
        if ($this->repo->findOneBy(['code' => $dto->code])) {
            throw new \DomainException('Ce code d\'emplacement existe déjà.');
        }

        $type = $this->typeRepo->find($dto->typeEmplacementId);
        if (!$type) {
            throw new \DomainException('TypeEmplacement introuvable.');
        }

        $emplacement = new Emplacement();
        $emplacement->setCode($dto->code)
                    ->setDescription($dto->description)
                    ->setTypeEmplacement($type)
                    ->setDossier($this->dossierContext->getCurrentOrThrow());

        if ($user) {
            $emplacement->setCreatedBy($user);
        }
        $this->em->persist($emplacement);
        $this->em->flush();

        return $emplacement;
    }

    public function update(Emplacement $emplacement, EmplacementDTO $dto, ?Utilisateur $user = null): Emplacement
    {
        $existing = $this->repo->findOneBy(['code' => $dto->code]);
        if ($existing && $existing->getId() !== $emplacement->getId()) {
            throw new \DomainException('Ce code d\'emplacement existe déjà.');
        }

        $type = $this->typeRepo->find($dto->typeEmplacementId);
        if (!$type) {
            throw new \DomainException('TypeEmplacement introuvable.');
        }

        $emplacement->setCode($dto->code)
                    ->setDescription($dto->description)
                    ->setTypeEmplacement($type);

        if ($user) {
            $emplacement->setUpdatedBy($user);
        }
        $this->em->flush();

        return $emplacement;
    }

    public function delete(Emplacement $emplacement): void
    {
        $this->em->remove($emplacement);
        $this->em->flush();
    }

    public function normalize(Emplacement $e): array
    {
        return [
            'id'              => $e->getId(),
            'code'            => $e->getCode(),
            'description'     => $e->getDescription(),
            'typeEmplacement' => [
                'id'      => $e->getTypeEmplacement()->getId(),
                'libelle' => $e->getTypeEmplacement()->getLibelle(),
            ],
        ];
    }
}
