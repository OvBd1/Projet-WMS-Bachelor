<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class StockUpdateDTO
{
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    public int $quantite = 0;
}
