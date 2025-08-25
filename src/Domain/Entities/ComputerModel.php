<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_computermodels')]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'product_number', columns: ['product_number'])]
class ComputerModel
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
    private $weight;

    #[ORM\Column(name: 'required_units', type: 'integer', options: ['default' => 1])]
    private $requiredUnits;

    #[ORM\Column(name: 'depth', type: "float", options: ["default" => 1], columnDefinition: "FLOAT NOT NULL DEFAULT 1")]
    private $depth;

    #[ORM\Column(name: 'power_connections', type: 'integer', options: ['default' => 0])]
    private $powerConnections;

    #[ORM\Column(name: 'power_consumption', type: 'integer', options: ['default' => 0])]
    private $powerConsumption;

    #[ORM\Column(name: 'is_half_rack', type: 'boolean', options: ['default' => 0])]
    private $isHalfRack;

    #[ORM\Column(name: 'picture_front', type: 'text', length: 65535, nullable: true)]
    private $pictureFront;

    #[ORM\Column(name: 'picture_rear', type: 'text', length: 65535, nullable: true)]
    private $pictureRear;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: false)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: false)]
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

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getRequiredUnits(): ?int
    {
        return $this->requiredUnits;
    }

    public function setRequiredUnits(int $requiredUnits): self
    {
        $this->requiredUnits = $requiredUnits;

        return $this;
    }

    public function getDepth(): ?float
    {
        return $this->depth;
    }

    public function setDepth(float $depth): self
    {
        $this->depth = $depth;

        return $this;
    }

    public function getPowerConnections(): ?int
    {
        return $this->powerConnections;
    }

    public function setPowerConnections(int $powerConnections): self
    {
        $this->powerConnections = $powerConnections;

        return $this;
    }

    public function getPowerConsumption(): ?int
    {
        return $this->powerConsumption;
    }

    public function setPowerConsumption(int $powerConsumption): self
    {
        $this->powerConsumption = $powerConsumption;

        return $this;
    }

    public function getIsHalfRack(): ?int
    {
        return $this->isHalfRack;
    }

    public function setIsHalfRack(int $isHalfRack): self
    {
        $this->isHalfRack = $isHalfRack;

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

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }
}
