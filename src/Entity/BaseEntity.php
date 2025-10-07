<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Behaviour\TimestampableBehaviour;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

abstract class BaseEntity
{
    use TimestampableBehaviour;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:"SEQUENCE")]
    #[ORM\Column]
    #[Groups(['read', 'update'])]
    protected ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

}
