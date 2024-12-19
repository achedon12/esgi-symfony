<?php

namespace App\Trait;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait TimeStampTrait
{

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function getCreationDate(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreationDate(DateTimeImmutable $date = new DateTimeImmutable()): static
    {
        $this->createdAt = $date;

        return $this;
    }

    public function getUpdateDate(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdateDate(DateTimeImmutable $date = new DateTimeImmutable()): static
    {
        $this->updatedAt = $date;

        return $this;
    }

}