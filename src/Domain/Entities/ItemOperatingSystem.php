<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_operatingsystems")]
#[ORM\UniqueConstraint(columns: ["items_id", "itemtype", "operatingsystems_id", "operatingsystemarchitectures_id"])]
#[ORM\Index(columns: ["items_id"])]
#[ORM\Index(columns: ["itemtype", "items_id"])]
#[ORM\Index(columns: ["operatingsystems_id"])]
#[ORM\Index(columns: ["operatingsystemservicepacks_id"])]
#[ORM\Index(columns: ["operatingsystemversions_id"])]
#[ORM\Index(columns: ["operatingsystemarchitectures_id"])]
#[ORM\Index(columns: ["operatingsystemkernelversions_id"])]
#[ORM\Index(columns: ["operatingsystemeditions_id"])]
#[ORM\Index(columns: ["is_deleted"])]
#[ORM\Index(columns: ["is_dynamic"])]
#[ORM\Index(columns: ["entities_id"])]
#[ORM\Index(columns: ["is_recursive"])]
class ItemOperatingSystem {

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: "integer")]
	private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $items_id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $itemtype;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $operatingsystems_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $operatingsystemversions_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $operatingsystemservicepacks_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $operatingsystemarchitectures_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $operatingsystemkernelversions_id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $license_number;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $licenseid;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $operatingsystemeditions_id;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_deleted;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_dynamic;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_recursive;


    public function getId(): ?int {
        return $this->id;
    }

    public function getItemsId(): ?int {
        return $this->items_id;
    }

    public function setItemsId(int $items_id): self {
        $this->items_id = $items_id;

        return $this;
    }

    public function getItemtype(): ?string {
        return $this->itemtype;
    }

    public function setItemtype(?string $itemtype): self {
        $this->itemtype = $itemtype;

        return $this;
    }

    public function getOperatingSystemsId(): ?int {
        return $this->operatingsystems_id;
    }

    public function setOperatingSystemsId(int $operatingsystems_id): self {
        $this->operatingsystems_id = $operatingsystems_id;

        return $this;
    }

    public function getOperatingSystemVersionsId(): ?int {
        return $this->operatingsystemversions_id;
    }

    public function setOperatingSystemVersionsId(int $operatingsystemversions_id): self {
        $this->operatingsystemversions_id = $operatingsystemversions_id;

        return $this;
    }

    public function getOperatingSystemServicePacksId(): ?int {
        return $this->operatingsystemservicepacks_id;
    }

    public function setOperatingSystemServicePacksId(int $operatingsystemservicepacks_id): self {
        $this->operatingsystemservicepacks_id = $operatingsystemservicepacks_id;

        return $this;
    }

    public function getOperatingSystemArchitecturesId(): ?int {
        return $this->operatingsystemarchitectures_id;
    }

    public function setOperatingSystemArchitecturesId(int $operatingsystemarchitectures_id): self {
        $this->operatingsystemarchitectures_id = $operatingsystemarchitectures_id;

        return $this;
    }

    public function getOperatingSystemKernelVersionsId(): ?int {
        return $this->operatingsystemkernelversions_id;
    }

    public function setOperatingSystemKernelVersionsId(int $operatingsystemkernelversions_id): self {
        $this->operatingsystemkernelversions_id = $operatingsystemkernelversions_id;

        return $this;
    }

    public function getLicenseNumber(): ?string {
        return $this->license_number;
    }

    public function setLicenseNumber(string $license_number): self {
        $this->license_number = $license_number;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function isIsDeleted(): ?bool {
        return $this->is_deleted;
    }

    public function setIsDeleted(bool $deleted): self {
        $this->is_deleted = $deleted;

        return $this;
    }

    public function getIsDynamic(): ?bool {
        return $this->is_dynamic;
    }

    public function setIsDynamic(bool $is_dynamic): self {
        $this->is_dynamic = $is_dynamic;

        return $this;
    }

    public function getEntitiesId(): ?int {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool {
        return $this->is_recursive;
    }

    public function setIsRecursive(bool $is_recursive): self {
        $this->is_recursive = $is_recursive;

        return $this;
    }
}
