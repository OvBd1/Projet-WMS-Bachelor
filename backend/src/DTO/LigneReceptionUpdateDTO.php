<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class LigneReceptionUpdateDTO
{
    #[Assert\NotNull]
    #[Assert\Positive]
    public int $emplacementId = 0;

    #[Assert\NotNull]
    #[Assert\Positive]
    public int $quantite = 0;

    public ?string $dlc = null;

    #[Assert\Length(max: 100)]
    public ?string $numeroSerie = null;
}
