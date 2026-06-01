<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class TiersDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $nom = '';

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['FOURNISSEUR', 'CLIENT', 'AUTRE'])]
    public string $type = 'FOURNISSEUR';

    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public ?string $email = null;

    #[Assert\Length(max: 30)]
    public ?string $telephone = null;

    public ?string $adresse = null;
}
