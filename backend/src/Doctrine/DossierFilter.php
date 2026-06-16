<?php

namespace App\Doctrine;

use App\Traits\DossierScopedTrait;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class DossierFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        if (!\in_array(DossierScopedTrait::class, $targetEntity->reflClass->getTraitNames(), true)) {
            return '';
        }

        if (!$this->hasParameter('dossierId')) {
            return '';
        }

        return sprintf('%s.dossier_id = %s', $targetTableAlias, $this->getParameter('dossierId'));
    }
}
