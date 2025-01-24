<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_operatingsystems")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["items_id", "itemtype", "operatingsystems_id", "operatingsystemarchitectures_id"])]
#[ORM\Index(name: "items_id", columns: ["items_id"])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id"])]
#[ORM\Index(name: "operatingsystems_id", columns: ["operatingsystems_id"])]
#[ORM\Index(name: "operatingsystemservicepacks_id", columns: ["operatingsystemservicepacks_id"])]
#[ORM\Index(name: "operatingsystemversions_id", columns: ["operatingsystemversions_id"])]
#[ORM\Index(name: "operatingsystemarchitectures_id", columns: ["operatingsystemarchitectures_id"])]
#[ORM\Index(name: "operatingsystemkernelversions_id", columns: ["operatingsystemkernelversions_id"])]
#[ORM\Index(name: "operatingsystemeditions_id", columns: ["operatingsystemeditions_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
class ItemOperatingSystem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'items_id', type: "integer", options: ["default" => 0])]
    private $itemsId;

    #[ORM\Column(name: 'itemtype', type: "string", length: 255, nullable: true)]
    private $itemtype;

    #[ORM\Column(name: 'operatingsystems_id', type: "integer", options: ["default" => 0])]
    private $operatingsystemsId;

    #[ORM\Column(name: 'operatingsystemversions_id', type: "integer", options: ["default" => 0])]
    private $operatingsystemversionsId;

    #[ORM\Column(name: 'operatingsystemservicepacks_id', type: "integer", options: ["default" => 0])]
    private $operatingsystemservicepacksId;

    #[ORM\Column(name: 'operatingsystemarchitectures_id', type: "integer", options: ["default" => 0])]
    private $operatingsystemarchitecturesId;

    #[ORM\Column(name: 'operatingsystemkernelversions_id', type: "integer", options: ["default" => 0])]
    private $operatingsystemkernelversionsId;

    #[ORM\Column(name: 'license_number', type: "string", length: 255, nullable: true)]
    private $licenseNumber;

    #[ORM\Column(name: 'licenseid', type: "string", length: 255, nullable: true)]
    private $licenseid;

    #[ORM\Column(name: 'operatingsystemeditions_id', type: "integer", options: ["default" => 0])]
    private $operatingsystemeditionsId;

    #[ORM\Column(name: 'date_mod', type: "datetime", nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: "datetime", nullable: true)]
    private $dateCreation;

    #[ORM\Column(name: 'is_deleted', type: "boolean", options: ["default" => 0])]
    private $isDeleted;

    #[ORM\Column(name: 'is_dynamic', type: "boolean", options: ["default" => 0])]
    private $isDynamic;

    #[ORM\Column(name: 'entities_id', type: "integer", options: ["default" => 0])]
    private $entitiesId;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => 0])]
    private $isRecursive;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->itemsId;
    }

    public function setItemsId(int $itemsId): self
    {
        $this->itemsId = $itemsId;

        return $this;
    }

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(?string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
    }

    public function getOperatingSystemsId(): ?int
    {
        return $this->operatingsystemsId;
    }

    public function setOperatingSystemsId(int $operatingsystemsId): self
    {
        $this->operatingsystemsId = $operatingsystemsId;

        return $this;
    }

    public function getOperatingSystemVersionsId(): ?int
    {
        return $this->operatingsystemversionsId;
    }

    public function setOperatingSystemVersionsId(int $operatingsystemversionsId): self
    {
        $this->operatingsystemversionsId = $operatingsystemversionsId;

        return $this;
    }

    public function getOperatingSystemServicePacksId(): ?int
    {
        return $this->operatingsystemservicepacksId;
    }

    public function setOperatingSystemServicePacksId(int $operatingsystemservicepacksId): self
    {
        $this->operatingsystemservicepacksId = $operatingsystemservicepacksId;

        return $this;
    }

    public function getOperatingSystemArchitecturesId(): ?int
    {
        return $this->operatingsystemarchitecturesId;
    }

    public function setOperatingSystemArchitecturesId(int $operatingsystemarchitecturesId): self
    {
        $this->operatingsystemarchitecturesId = $operatingsystemarchitecturesId;

        return $this;
    }

    public function getOperatingSystemKernelVersionsId(): ?int
    {
        return $this->operatingsystemkernelversionsId;
    }

    public function setOperatingSystemKernelVersionsId(int $operatingsystemkernelversionsId): self
    {
        $this->operatingsystemkernelversionsId = $operatingsystemkernelversionsId;

        return $this;
    }

    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    public function setLicenseNumber(string $licenseNumber): self
    {
        $this->licenseNumber = $licenseNumber;

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

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $deleted): self
    {
        $this->isDeleted = $deleted;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->isDynamic;
    }

    public function setIsDynamic(bool $isDynamic): self
    {
        $this->isDynamic = $isDynamic;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entitiesId;
    }

    public function setEntitiesId(int $entitiesId): self
    {
        $this->entitiesId = $entitiesId;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

        return $this;
    }
}
