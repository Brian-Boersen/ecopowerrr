<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\MothlyYieldRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MothlyYieldRepository::class)]
// #[ApiResource]
class MothlyYield
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devices $device = null;

    #[ORM\Column]
    private ?string $serial_number = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $start_date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $end_date = null;

    #[ORM\Column]
    private ?float $yield = null;

    #[ORM\Column]
    private ?float $surplus = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDevice(): ?devices
    {
        return $this->device;
    }

    public function setDevice(?devices $device): self
    {
        $this->device = $device;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serial_number;
    }

    public function setSerialNumber(string $serial_number): self
    {
        $this->serial_number = $serial_number;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeInterface $start_date): self
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function getYield(): ?float
    {
        return $this->yield;
    }

    public function setYield(float $yield): self
    {
        $this->yield = $yield;

        return $this;
    }

    public function getSurplus(): ?float
    {
        return $this->surplus;
    }

    public function setSurplus(float $surplus): self
    {
        $this->surplus = $surplus;

        return $this;
    }
}
