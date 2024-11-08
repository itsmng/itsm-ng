<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

//id	int(11) Auto Increment
//designation	varchar(255) NULL
//devicecasetypes_id	int(11) [0]
//comment	text NULL
//manufacturers_id	int(11) [0]
//entities_id	int(11) [0]
//is_recursive	tinyint(1) [0]
//devicecasemodels_id	int(11) NULL
//date_mod	timestamp NULL
//date_creation	timestamp NULL
//Indexes
//PRIMARY	id
//INDEX	designation
//INDEX	manufacturers_id
//INDEX	devicecasetypes_id
//INDEX	entities_id
//INDEX	is_recursive
//INDEX	date_mod
//INDEX	date_creation
//INDEX	devicecasemodels_id

#[ORM\Entity]
#[ORM\Table(name: "glpi_devicecases")]
#[ORM\Index(name: "designation", columns: ["designation"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "devicecasetypes_id", columns: ["devicecasetypes_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "devicecasemodels_id", columns: ["devicecasemodels_id"])]
class DeviceCase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $devicecasetypes_id;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $manufacturers_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    #[ORM\Column(type: "integer", nullable: true)]
    private $devicecasemodels_id;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getDevicecasetypesId(): ?int
    {
        return $this->devicecasetypes_id;
    }

    public function setDevicecasetypesId(int $devicecasetypes_id): self
    {
        $this->devicecasetypes_id = $devicecasetypes_id;

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

    public function setManufacturersId(int $manufacturers_id): self
    {
        $this->manufacturers_id = $manufacturers_id;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getDevicecasemodelsId(): ?int
    {
        return $this->devicecasemodels_id;
    }

    public function setDevicecasemodelsId(?int $devicecasemodels_id): self
    {
        $this->devicecasemodels_id = $devicecasemodels_id;

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
