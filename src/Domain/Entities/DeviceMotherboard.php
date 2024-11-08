<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_devicemotherboards")]
#[ORM\Index(name: "designation", columns: ["designation"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "devicemotherboardmodels_id", columns: ["devicemotherboardmodels_id"])]
class DeviceMotherboard {

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: "integer")]
	private $id;

	#[ORM\Column(type: "string", length: 255, nullable: true)]
	private $designation;

	#[ORM\Column(type: "string", length: 255, nullable: true)]
	private $chipset;

	#[ORM\Column(type: "text", nullable: true, length: 65535)]
	private $comment;

	#[ORM\Column(type: "integer", options: ["default" => 0])]
    private $manufacturers_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

	#[ORM\Column(type: "boolean", options: ["default" => 0])]
	private $is_recursive;

	#[ORM\Column(type: "integer", nullable: true)]
	private $devicemotherboardmodels_id;

	#[ORM\Column(type: "datetime", nullable: true)]
	private $date_mod;

	#[ORM\Column(type: "datetime", nullable: true)]
	private $date_creation;

    function getId(): ?int
    {
        return $this->id;
    }

    function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    function getDesignation(): ?string
    {
        return $this->designation;
    }

    function setDesignation(?string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    function getChipset(): ?string
    {
        return $this->chipset;
    }

    function setChipset(?string $chipset): self
    {
        $this->chipset = $chipset;

        return $this;
    }

    function getComment(): ?string
    {
        return $this->comment;
    }

    function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    function getManufacturersId(): ?int
    {
        return $this->manufacturers_id;
    }

    function setManufacturersId(?int $manufacturers_id): self
    {
        $this->manufacturers_id = $manufacturers_id;

        return $this;
    }

    function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    function setEntitiesId(?int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    function getDevicemotherboardmodelsId(): ?int
    {
        return $this->devicemotherboardmodels_id;
    }

    function setDevicemotherboardmodelsId(?int $devicemotherboardmodels_id): self
    {
        $this->devicemotherboardmodels_id = $devicemotherboardmodels_id;

        return $this;
    }

    function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }
}
