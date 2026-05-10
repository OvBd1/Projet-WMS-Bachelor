<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class TypeEmplacementDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $libelle = '';
}
