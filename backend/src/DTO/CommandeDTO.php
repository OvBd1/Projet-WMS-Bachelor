<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CommandeDTO
{
    public ?string $dateCommande = null;

    /** @var LigneCommandeDTO[] */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1)]
    public array $lignes = [];
}
