<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ArticleDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    public string $reference = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $libelle = '';

    public ?string $description = null;

    public bool $gestionDlc = false;

    public bool $gestionNumeroSerie = false;

    public ?int $typeConditionnementId = null;
}
