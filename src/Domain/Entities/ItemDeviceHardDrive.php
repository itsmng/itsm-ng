<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

//Table: glpi_items_deviceharddrives
//
//Select data Show structure Alter table New item
//Column	Type	Comment
//id	int(11) Auto Increment
//items_id	int(11) [0]
//itemtype	varchar(255) NULL
//deviceharddrives_id	int(11) [0]
//capacity	int(11) [0]
//serial	varchar(255) NULL
//is_deleted	tinyint(1) [0]
//is_dynamic	tinyint(1) [0]
//entities_id	int(11) [0]
//is_recursive	tinyint(1) [0]
//busID	varchar(255) NULL
//otherserial	varchar(255) NULL
//locations_id	int(11) [0]
//states_id	int(11) [0]
//Indexes
//PRIMARY	id
//INDEX	items_id
//INDEX	deviceharddrives_id
//INDEX	capacity
//INDEX	is_deleted
//INDEX	is_dynamic
//INDEX	serial
//INDEX	entities_id
//INDEX	is_recursive
//INDEX	busID
//INDEX	itemtype, items_id
//INDEX	otherserial
//INDEX	locations_id
//INDEX	states_id

#[ORM\Entity]
#[ORM\Table(name: 'glpi_items_deviceharddrives')]
#[ORM\Index(columns: ['items_id'])]
#[ORM\Index(columns: ['deviceharddrives_id'])]
#[ORM\Index(columns: ['capacity'])]
#[ORM\Index(columns: ['is_deleted'])]
#[ORM\Index(columns: ['is_dynamic'])]
#[ORM\Index(columns: ['serial'])]
#[ORM\Index(columns: ['entities_id'])]
#[ORM\Index(columns: ['is_recursive'])]
#[ORM\Index(columns: ['busID'])]
#[ORM\Index(columns: ['itemtype', 'items_id'])]
#[ORM\Index(columns: ['otherserial'])]
#[ORM\Index(columns: ['locations_id'])]
#[ORM\Index(columns: ['states_id'])]
class ItemDeviceHardDrive
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $deviceharddrives_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $capacity;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_deleted;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_dynamic;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $busID;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $locations_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
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

    public function getDeviceharddrivesId(): ?int
    {
        return $this->deviceharddrives_id;
    }

    public function setDeviceharddrivesId(int $deviceharddrives_id): self
    {
        $this->deviceharddrives_id = $deviceharddrives_id;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

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

    public function getBusID(): ?string
    {
        return $this->busID;
    }

    public function setBusID(?string $busID): self
    {
        $this->busID = $busID;

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
