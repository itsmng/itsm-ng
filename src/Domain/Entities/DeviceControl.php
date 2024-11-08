<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

//Table: glpi_devicecontrols
//
//Select data Show structure Alter table New item
//Column	Type	Comment
//id	int(11) Auto Increment
//designation	varchar(255) NULL
//is_raid	tinyint(1) [0]
//comment	text NULL
//manufacturers_id	int(11) [0]
//interfacetypes_id	int(11) [0]
//entities_id	int(11) [0]
//is_recursive	tinyint(1) [0]
//devicecontrolmodels_id	int(11) NULL
//date_mod	timestamp NULL
//date_creation	timestamp NULL
//Indexes
//PRIMARY	id
//INDEX	designation
//INDEX	manufacturers_id
//INDEX	interfacetypes_id
//INDEX	entities_id
//INDEX	is_recursive
//INDEX	date_mod
//INDEX	date_creation
//INDEX	devicecontrolmodels_id

#[ORM\Entity]
#[ORM\Table(name: 'glpi_devicecontrols')]
#[ORM\Index(name: 'designation', columns: ['designation'])]
#[ORM\Index(name: 'manufacturers_id', columns: ['manufacturers_id'])]
#[ORM\Index(name: 'interfacetypes_id', columns: ['interfacetypes_id'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'devicecontrolmodels_id', columns: ['devicecontrolmodels_id'])]
class DeviceControl
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_raid;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $manufacturers_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $interfacetypes_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $devicecontrolmodels_id;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getIsRaid(): ?bool
    {
        return $this->is_raid;
    }

    public function setIsRaid(?bool $isRaid): self
    {
        $this->is_raid = $isRaid;

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

    public function getManufacturersId(): ?int
    {
        return $this->manufacturers_id;
    }

    public function setManufacturersId(?int $manufacturersId): self
    {
        $this->manufacturers_id = $manufacturersId;

        return $this;
    }

    public function getInterfacetypesId(): ?int
    {
        return $this->interfacetypes_id;
    }

    public function setInterfacetypesId(?int $interfacetypesId): self
    {
        $this->interfacetypes_id = $interfacetypesId;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(?int $entitiesId): self
    {
        $this->entities_id = $entitiesId;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->is_recursive = $isRecursive;

        return $this;
    }

    public function getDevicecontrolmodelsId(): ?int
    {
        return $this->devicecontrolmodels_id;
    }

    public function setDevicecontrolmodelsId(?int $devicecontrolmodelsId): self
    {
        $this->devicecontrolmodels_id = $devicecontrolmodelsId;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTimeInterface $dateMod): self
    {
        $this->date_mod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->date_creation = $dateCreation;

        return $this;
    }
}
