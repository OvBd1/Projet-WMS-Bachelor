<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CommandeStatutDTO
{
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['EN_ATTENTE', 'PREPAREE', 'EXPEDIEE', 'ANNULEE'])]
    public string $statut = '';
}
