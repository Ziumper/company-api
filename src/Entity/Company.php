<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\UniqueConstraint(fields:["taxReferenceNumber"], name:"tax_reference_number")]
#[ORM\HasLifecycleCallbacks]
class Company extends BaseEntity
{
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups:["Default"])]
    #[Groups(['read','create','update'])]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(groups:["Default"])]
    #[Assert\Regex('^\d{10}$^', groups:['Default'])]
    #[Groups(['read','create','update'])]
    private ?string $taxReferenceNumber = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups:["Default"])]
    #[Groups(['read','create','update'])]
    private ?string $street = null;

    #[ORM\Column(length: 6)]
    #[Assert\NotBlank(groups:['Default'])]
    #[Assert\Regex('^\d{2}-\d{3}$^', groups:['Default'])]
    #[Groups(['read','create','update'])]
    private ?string $zipcode = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups:["Default"])]
    #[Groups(['read','create','update'])]
    private ?string $town = null;

    #[ORM\OneToMany(
        targetEntity: Employee::class,
        mappedBy: 'company',
        orphanRemoval: true,
        cascade: ['persist','remove']
    )]
    #[Groups(['read','create'])]
    private Collection $employeers;


    public function __construct()
    {
        $this->employeers = new ArrayCollection();
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getTaxReferenceNumber(): ?string
    {
        return $this->taxReferenceNumber;
    }

    public function setTaxReferenceNumber(string $taxReferenceNumber): static
    {
        $this->taxReferenceNumber = $taxReferenceNumber;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode): static
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(string $town): static
    {
        $this->town = $town;

        return $this;
    }

    /**
     * @return Collection<int, Employee>
     */
    public function getEmployeers(): Collection
    {
        return $this->employeers;
    }

    public function addEmployeer(Employee $employeer): static
    {
        if (!$this->employeers->contains($employeer)) {
            $this->employeers->add($employeer);
            $employeer->setCompany($this);
        }

        return $this;
    }

    public function removeEmployeer(Employee $employeer): static
    {
        if ($this->employeers->removeElement($employeer)) {
            // set the owning side to null (unless already changed)
            if ($employeer->getCompany() === $this) {
                $employeer->setCompany(null);
            }
        }

        return $this;
    }
}
