<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_items_devicegraphiccards')]
#[ORM\Index(name: "items_id", columns: ['items_id'])]
#[ORM\Index(name: "devicegraphiccards_id", columns: ['devicegraphiccards_id'])]
#[ORM\Index(name: "specificity", columns: ['memory'])]
#[ORM\Index(name: "is_deleted", columns: ['is_deleted'])]
#[ORM\Index(name: "is_dynamic", columns: ['is_dynamic'])]
#[ORM\Index(name: "entities_id", columns: ['entities_id'])]
#[ORM\Index(name: "is_recursive", columns: ['is_recursive'])]
#[ORM\Index(name: "serial", columns: ['serial'])]
#[ORM\Index(name: "busID", columns: ['busID'])]
#[ORM\Index(name: "item", columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: "otherserial", columns: ['otherserial'])]
#[ORM\Index(name: "locations_id", columns: ['locations_id'])]
#[ORM\Index(name: "states_id", columns: ['states_id'])]
class ItemDeviceGraphicCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $itemsId;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 255, nullable: true)]
    private $itemtype;

    #[ORM\Column(name: 'devicegraphiccards_id', type: 'integer', options: ['default' => 0])]
    private $devicegraphiccardsId;

    #[ORM\Column(name: 'memory', type: 'integer', options: ['default' => 0])]
    private $memory;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted;

    #[ORM\Column(name: 'is_dynamic', type: 'boolean', options: ['default' => 0])]
    private $isDynamic;

    #[ORM\Column(name: 'entities_id', type: 'integer', options: ['default' => 0])]
    private $entitiesId;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'serial', type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(name: 'busID', type: 'string', length: 255, nullable: true)]
    private $busID;

    #[ORM\Column(name: 'otherserial', type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\Column(name: 'locations_id', type: 'integer', options: ['default' => 0])]
    private $locationsId;

    #[ORM\Column(name: 'states_id', type: 'integer', options: ['default' => 0])]
    private $statesId;

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

    public function getDeviceGraphicCardsId(): ?int
    {
        return $this->devicegraphiccardsId;
    }

    public function setDeviceGraphicCardsId(int $devicegraphiccardsId): self
    {
        $this->devicegraphiccardsId = $devicegraphiccardsId;

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
        return $this->isDeleted;
    }

    public function setIsDeleted(int $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getIsDynamic(): ?int
    {
        return $this->isDynamic;
    }

    public function setIsDynamic(int $isDynamic): self
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

    public function getIsRecursive(): ?int
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(int $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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

    public function setBusId(?string $busId): self
    {
        $this->busID = $busId;

        return $this;
    }

    public function getOtherSerial(): ?string
    {
        return $this->otherserial;
    }

    public function setOtherSerial(?string $otherSerial): self
    {
        $this->otherserial = $otherSerial;

        return $this;
    }

    public function getLocationsId(): ?int
    {
        return $this->locationsId;
    }

    public function setLocationsId(?int $locationsId): self
    {
        $this->locationsId = $locationsId;

        return $this;
    }

    public function getStatesId(): ?int
    {
        return $this->statesId;
    }

    public function setStatesId(?int $statesId): self
    {
        $this->statesId = $statesId;

        return $this;
    }
}
