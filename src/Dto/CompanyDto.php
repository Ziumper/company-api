<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CompanyDto {

    #[Assert\NotBlank(groups:["postValidation"])]
    public string $name;
    #[Assert\NotBlank(groups:["postValidation"])]
    #[Assert\Regex('^\d{10}$^')]
    public string $taxReferenceNumber;
    #[Assert\NotBlank(groups:['postValidation'])]
    #[Assert\Regex('^\d{2}-\d{3}$^')]
    public string $zipcode;
    #[Assert\NotBlank(groups:["postValidation"])]
    public string $street;
    #[Assert\NotBlank(groups:["postValidation"])]
    public string $town;


}