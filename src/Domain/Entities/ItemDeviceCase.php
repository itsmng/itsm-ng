<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_devicecases")]
#[ORM\Index(columns: ["items_id"])]
#[ORM\Index(columns: ["devicecases_id"])]
#[ORM\Index(columns: ["is_deleted"])]
#[ORM\Index(columns: ["is_dynamic"])]
#[ORM\Index(columns: ["entities_id"])]
#[ORM\Index(columns: ["is_recursive"])]
#[ORM\Index(columns: ["serial"])]
#[ORM\Index(columns: ["itemtype", "items_id"])]
#[ORM\Index(columns: ["otherserial"])]
#[ORM\Index(columns: ["locations_id"])]
#[ORM\Index(columns: ["states_id"])]
class ItemDeviceCase
{
    #[ORM\Id]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(name: "items_id", type: "integer", options: ["default" => 0])]
    private int $items_id;

    #[ORM\Column(name: "itemtype", type: "string", length: 255, nullable: true)]
    private string $itemtype;

    #[ORM\Column(name: "devicecases_id", type: "integer", options: ["default" => 0])]
    private int $devicecases_id;

    #[ORM\Column(name: "is_deleted", type: "boolean", options: ["default" => 0])]
    private bool $is_deleted;

    #[ORM\Column(name: "is_dynamic", type: "boolean", options: ["default" => 0])]
    private bool $is_dynamic;

    #[ORM\Column(name: "entities_id", type: "integer", options: ["default" => 0])]
    private int $entities_id;

    #[ORM\Column(name: "is_recursive", type: "boolean", options: ["default" => 0])]
    private bool $is_recursive;

    #[ORM\Column(name: "serial", type: "string", length: 255, nullable: true)]
    private string $serial;

    #[ORM\Column(name: "otherserial", type: "string", length: 255, nullable: true)]
    private string $otherserial;

    #[ORM\Column(name: "locations_id", type: "integer", options: ["default" => 0])]
    private int $locations_id;

    #[ORM\Column(name: "states_id", type: "integer", options: ["default" => 0])]
    private int $states_id;

    public function getId(): int
    {
        return $this->id;
    }

    public function getItemsId(): int
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

    public function getDevicecasesId(): int
    {
        return $this->devicecases_id;
    }

    public function setDevicecasesId(int $devicecases_id): self
    {
        $this->devicecases_id = $devicecases_id;

        return $this;
    }

    public function getIsDeleted(): bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getIsDynamic(): bool
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(bool $is_dynamic): self
    {
        $this->is_dynamic = $is_dynamic;

        return $this;
    }

    public function getEntitiesId(): int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): bool
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

    public function getLocationsId(): int
    {
        return $this->locations_id;
    }

    public function setLocationsId(int $locations_id): self
    {
        $this->locations_id = $locations_id;

        return $this;
    }

    public function getStatesId(): int
    {
        return $this->states_id;
    }

    public function setStatesId(int $states_id): self
    {
        $this->states_id = $states_id;

        return $this;
    }
}
