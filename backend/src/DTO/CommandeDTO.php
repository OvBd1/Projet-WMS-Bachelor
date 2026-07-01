<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CommandeDTO
{
    public ?int $tiersId = null;

    public ?string $dateExpedition = null;

    /** @var LigneCommandeDTO[] */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1)]
    public array $lignes = [];
}
