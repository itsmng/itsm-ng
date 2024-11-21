<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_networkequipmentmodels')]
#[ORM\Index(columns: ['name'])]
#[ORM\Index(columns: ['date_mod'])]
#[ORM\Index(columns: ['date_creation'])]
#[ORM\Index(columns: ['product_number'])]
class Networkequipmentmodel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $product_number;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $weight;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $required_units;

    #[ORM\Column(type: 'float', options: ['default' => 1.0])]
    private $depth;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $power_connections;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $power_consumption;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_half_rack;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $picture_front;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $picture_rear;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getProductNumber(): ?string
    {
        return $this->product_number;
    }

    public function setProductNumber(?string $product_number): self
    {
        $this->product_number = $product_number;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getRequiredUnits(): ?int
    {
        return $this->required_units;
    }

    public function setRequiredUnits(?int $required_units): self
    {
        $this->required_units = $required_units;

        return $this;
    }

    public function getDepth(): ?float
    {
        return $this->depth;
    }

    public function setDepth(?float $depth): self
    {
        $this->depth = $depth;

        return $this;
    }

    public function getPowerConnections(): ?int
    {
        return $this->power_connections;
    }

    public function setPowerConnections(?int $power_connections): self
    {
        $this->power_connections = $power_connections;

        return $this;
    }

    public function getPowerConsumption(): ?int
    {
        return $this->power_consumption;
    }

    public function setPowerConsumption(?int $power_consumption): self
    {
        $this->power_consumption = $power_consumption;

        return $this;
    }

    public function getIsHalfRack(): ?bool
    {
        return $this->is_half_rack;
    }

    public function setIsHalfRack(?bool $is_half_rack): self
    {
        $this->is_half_rack = $is_half_rack;

        return $this;
    }

    public function getPictureFront(): ?string
    {
        return $this->picture_front;
    }

    public function setPictureFront(?string $picture_front): self
    {
        $this->picture_front = $picture_front;

        return $this;
    }

    public function getPictureRear(): ?string
    {
        return $this->picture_rear;
    }

    public function setPictureRear(?string $picture_rear): self
    {
        $this->picture_rear = $picture_rear;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }
}
