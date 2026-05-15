<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ReceptionDTO
{
    /** @var LigneReceptionDTO[] */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1)]
    public array $lignes = [];

    public ?int $tiersId = null;

    public ?string $dateReception = null;
}
