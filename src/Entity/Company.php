<?php

namespace App\Entity;

use App\Dto\CompanyDto;
use App\Repository\CompanyRepository;
use App\State\CompanyProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ApiResource]
#[ORM\UniqueConstraint(fields:["taxReferenceNumber"],name:"tax_reference_number")]
#[Post(input:CompanyDto::class, processor: CompanyProcessor::class,validationContext:
    ["groups" => ['Default','postValidation']])
]
#[Put(input:CompanyDto::class, processor: CompanyProcessor::class, validationContext:["groups" => ['postValidation']])]
#[Get]
#[Patch(input:CompanyDto::class, processor: CompanyProcessor::class)]
#[Delete]
#[GetCollection]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:"SEQUENCE")]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    private ?string $taxReferenceNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $street = null;

    #[ORM\Column(length: 6)]
    private ?string $zipcode = null;

    #[ORM\Column(length: 255)]
    private ?string $town = null;

    #[ORM\OneToMany(targetEntity: Employee::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $employeers;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->employeers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
