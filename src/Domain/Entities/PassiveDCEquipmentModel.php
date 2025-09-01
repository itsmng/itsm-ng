<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_passivedcequipmentmodels')]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'product_number', columns: ['product_number'])]
class PassiveDCEquipmentModel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'product_number', type: 'string', length: 255, nullable: true)]
    private $productNumber;

    #[ORM\Column(name: 'weight', type: 'integer', options: ['default' => 0])]
    private $weight = 0;

    #[ORM\Column(name: 'required_units', type: 'integer', options: ['default' => 1])]
    private $requiredUnits = 1;

    #[ORM\Column(name: 'depth', type: 'float', options: ['default' => 1])]
    private $depth = 1;

    #[ORM\Column(name: 'power_connections', type: 'integer', options: ['default' => 0])]
    private $powerConnections = 0;

    #[ORM\Column(name: 'power_consumption', type: 'integer', options: ['default' => 0])]
    private $powerConsumption = 0;

    #[ORM\Column(name: 'is_half_rack', type: 'boolean', options: ['default' => 0])]
    private $isHalfRack = 0;

    #[ORM\Column(name: 'picture_front', type: 'text', length: 65535, nullable: true)]
    private $pictureFront;

    #[ORM\Column(name: 'picture_rear', type: 'text', length: 65535, nullable: true)]
    private $pictureRear;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
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
        return $this->productNumber;
    }

    public function setProductNumber(?string $productNumber): self
    {
        $this->productNumber = $productNumber;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int|string $weight): self
    {
        $this->weight = (int) $weight;

        return $this;
    }

    public function getRequiredUnits(): ?int
    {
        return $this->requiredUnits;
    }

    public function setRequiredUnits(int|string $requiredUnits): self
    {
        $this->requiredUnits = (int) $requiredUnits;

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
        return $this->powerConnections;
    }

    public function setPowerConnections(int|string $powerConnections): self
    {
        $this->powerConnections = (int) $powerConnections;

        return $this;
    }

    public function getPowerConsumption(): ?int
    {
        return $this->powerConsumption;
    }

    public function setPowerConsumption(int|string $powerConsumption): self
    {
        $this->powerConsumption = (int) $powerConsumption;

        return $this;
    }

    public function getIsHalfRack(): ?int
    {
        return $this->isHalfRack;
    }

    public function setIsHalfRack(int|string $isHalfRack): self
    {
        $this->isHalfRack = (int) $isHalfRack;

        return $this;
    }

    public function getPictureFront(): ?string
    {
        return $this->pictureFront;
    }

    public function setPictureFront(?string $pictureFront): self
    {
        $this->pictureFront = $pictureFront;

        return $this;
    }

    public function getPictureRear(): ?string
    {
        return $this->pictureRear;
    }

    public function setPictureRear(?string $pictureRear): self
    {
        $this->pictureRear = $pictureRear;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(?\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

}
