<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class TiersDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    public string $code = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $nom = '';

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['FOURNISSEUR', 'CLIENT', 'AUTRE'])]
    public string $type = 'FOURNISSEUR';

    #[Assert\Length(max: 255)]
    public ?string $rue = null;

    #[Assert\Length(max: 20)]
    public ?string $codePostal = null;

    #[Assert\Length(max: 255)]
    public ?string $ville = null;

    #[Assert\Length(max: 100)]
    public ?string $pays = null;
}
