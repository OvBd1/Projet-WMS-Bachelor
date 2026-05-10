<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class EmplacementDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    public string $code = '';

    public ?string $description = null;

    #[Assert\NotNull]
    #[Assert\Positive]
    public int $typeEmplacementId = 0;
}
