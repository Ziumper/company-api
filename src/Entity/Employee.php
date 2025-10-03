<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Employee extends BaseEntity
{
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups:["Default"])]
    #[Groups(['read', 'create', 'update'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups:["Default"])]
    #[Groups(['read', 'create', 'update'])]
    private ?string $surname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups:["Default"])]
    #[Assert\Email(groups:["Default"])]
    #[Groups(['read', 'create', 'update'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read', 'create', 'update'])]
    private ?string $phoneNumber = null;

    #[ORM\ManyToOne(inversedBy: 'employeers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(groups:["Default"])]
    private ?Company $company = null;
    
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

}
