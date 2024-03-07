<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class EmployeeDto 
{
    #[Assert\NotBlank(groups:["postValidation"])]
    public string $name;
    
    #[Assert\NotBlank(groups:["postValidation"])]
    public string $surname;

    #[Assert\NotBlank(groups:["postValidation"])]
    #[Assert\Email(groups:["postValidation"])]
    public string $email;

    public ?string $phoneNumber = null;

    #[Assert\NotBlank(groups:["postValidation"])]
    public string $company;
}