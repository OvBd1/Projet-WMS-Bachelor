<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterDTO
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    public string $password = '';

    #[Assert\Choice(choices: ['ROLE_USER', 'ROLE_ADMIN'])]
    public string $role = 'ROLE_USER';
}
