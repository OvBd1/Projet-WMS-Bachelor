<?php

namespace App\Service;

use App\Entity\Dossier;
use App\Entity\Utilisateur;
use App\Repository\DossierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class DossierContext
{
    private bool $resolved = false;
    private ?Dossier $dossier = null;

    public function __construct(
        private Security $security,
        private RequestStack $requestStack,
        private DossierRepository $dossierRepo,
        private EntityManagerInterface $em,
    ) {}

    public function getCurrent(): ?Dossier
    {
        if ($this->resolved) {
            return $this->dossier;
        }
        $this->resolved = true;

        $user = $this->security->getUser();
        if ($user instanceof Utilisateur) {
            if (\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                $headerId = $this->requestStack->getCurrentRequest()?->headers->get('X-Dossier-Id');
                $this->dossier = $headerId ? $this->dossierRepo->find((int) $headerId) : null;
            } else {
                $this->dossier = $user->getDossier();
            }
        }

        $filter = $this->em->getFilters()->enable('dossier_filter');
        $filter->setParameter('dossierId', $this->dossier?->getId() ?? 0);

        return $this->dossier;
    }

    public function getCurrentOrThrow(): Dossier
    {
        return $this->getCurrent() ?? throw new \DomainException('Aucun dossier selectionne.');
    }
}
