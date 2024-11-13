<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_items_devicegraphiccards')]
#[ORM\Index(columns: ['items_id'])]
#[ORM\Index(columns: ['devicegraphiccards_id'])]
#[ORM\Index(columns: ['memory'])]
#[ORM\Index(columns: ['is_deleted'])]
#[ORM\Index(columns: ['is_dynamic'])]
#[ORM\Index(columns: ['entities_id'])]
#[ORM\Index(columns: ['is_recursive'])]
#[ORM\Index(columns: ['serial'])]
#[ORM\Index(columns: ['busID'])]
#[ORM\Index(columns: ['itemtype', 'items_id'])]
#[ORM\Index(columns: ['otherserial'])]
#[ORM\Index(columns: ['locations_id'])]
#[ORM\Index(columns: ['states_id'])]
class ItemDeviceGraphicCard
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
    private $devicegraphiccards_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $memory;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_dynamic;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serial;

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

    public function getDevicegraphiccardsId(): ?int
    {
        return $this->devicegraphiccards_id;
    }

    public function setDevicegraphiccardsId(int $devicegraphiccards_id): self
    {
        $this->devicegraphiccards_id = $devicegraphiccards_id;

        return $this;
    }

    public function getMemory(): ?int
    {
        return $this->memory;
    }

    public function setMemory(int $memory): self
    {
        $this->memory = $memory;

        return $this;
    }

    public function getIsDeleted(): ?int
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(int $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getIsDynamic(): ?int
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(int $is_dynamic): self
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

    public function getIsRecursive(): ?int
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(int $is_recursive): self
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

    public function getBusId(): ?string
    {
        return $this->busID;
    }

    public function setBusId(?string $bus_id): self
    {
        $this->busID = $bus_id;

        return $this;
    }

    public function getOtherSerial(): ?string
    {
        return $this->otherserial;
    }

    public function setOtherSerial(?string $other_serial): self
    {
        $this->otherserial = $other_serial;

        return $this;
    }

    public function getLocationsId(): ?int
    {
        return $this->locations_id;
    }

    public function setLocationsId(?int $locations_id): self
    {
        $this->locations_id = $locations_id;

        return $this;
    }

    public function getStatesId(): ?int
    {
        return $this->states_id;
    }

    public function setStatesId(?int $states_id): self
    {
        $this->states_id = $states_id;

        return $this;
    }
}
