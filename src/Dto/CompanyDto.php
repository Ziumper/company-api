<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CompanyDto {

    #[Assert\NotBlank]
    public string $name;
    #[Assert\NotBlank]
    #[Assert\Regex('^\d{10}$^')]
    public string $taxReferenceNumber;
    #[Assert\NotBlank]
    #[Assert\Regex('^\d{2}-\d{3}$^')]
    public string $zipcode;
    #[Assert\NotBlank]
    public string $street;
    #[Assert\NotBlank]
    public string $town;


}