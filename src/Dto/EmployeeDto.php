<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class EmployeeDto {

    #[Assert\NotBlank]
    public string $name;
    #[Assert\NotBlank]
    public string $surname;
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;
    public ?string $phoneNumber = null;
    #[Assert\NotBlank]
    public string $company;
}