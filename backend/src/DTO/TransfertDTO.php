<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class TransfertDTO
{
    #[Assert\NotNull]
    #[Assert\Positive]
    public int $articleId = 0;

    #[Assert\NotNull]
    #[Assert\Positive]
    public int $emplacementSourceId = 0;

    #[Assert\NotNull]
    #[Assert\Positive]
    public int $emplacementDestinationId = 0;

    #[Assert\NotNull]
    #[Assert\Positive]
    public int $quantite = 0;
}
