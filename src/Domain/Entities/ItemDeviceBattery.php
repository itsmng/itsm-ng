<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_devicebatteries")]
#[ORM\Index(columns: ["items_id"])]
#[ORM\Index(columns: ["devicebatteries_id"])]
#[ORM\Index(columns: ["is_deleted"])]
#[ORM\Index(columns: ["is_dynamic"])]
#[ORM\Index(columns: ["entities_id"])]
#[ORM\Index(columns: ["is_recursive"])]
#[ORM\Index(columns: ["serial"])]
#[ORM\Index(columns: ["itemtype", "items_id"])]
#[ORM\Index(columns: ["otherserial"])]
class ItemDeviceBattery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $items_id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $itemtype;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $devicebatteries_id;

    #[ORM\Column(type: "date", nullable: true)]
    private $manufacturing_date;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_deleted;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_dynamic;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $otherserial;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $locations_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $states_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->items_id;
    }

    public function setItemsId(int $items_id): self
    {
        $this->items_id = $items_id;

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

    public function getDevicebatteriesId(): ?int
    {
        return $this->devicebatteries_id;
    }

    public function setDevicebatteriesId(int $devicebatteries_id): self
    {
        $this->devicebatteries_id = $devicebatteries_id;

        return $this;
    }

    public function getManufacturingDate(): ?\DateTimeInterface
    {
        return $this->manufacturing_date;
    }

    public function setManufacturingDate(\DateTimeInterface $manufacturing_date): self
    {
        $this->manufacturing_date = $manufacturing_date;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(bool $is_dynamic): self
    {
        $this->is_dynamic = $is_dynamic;

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

    public function getSerial(): ?string
    {
        return $this->serial;
    }

    public function setSerial(?string $serial): self
    {
        $this->serial = $serial;

        return $this;
    }

    public function getOtherserial(): ?string
    {
        return $this->otherserial;
    }

    public function setOtherserial(?string $otherserial): self
    {
        $this->otherserial = $otherserial;

        return $this;
    }

    public function getLocationsId(): ?int
    {
        return $this->locations_id;
    }

    public function setLocationsId(int $locations_id): self
    {
        $this->locations_id = $locations_id;

        return $this;
    }

    public function getStatesId(): ?int
    {
        return $this->states_id;
    }

    public function setStatesId(int $states_id): self
    {
        $this->states_id = $states_id;

        return $this;
    }
}