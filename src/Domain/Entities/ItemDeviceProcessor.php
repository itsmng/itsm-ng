<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_items_deviceprocessors')]
#[ORM\Index(name: "items_id", columns: ['items_id'])]
#[ORM\Index(name: "deviceprocessors_id", columns: ['deviceprocessors_id'])]
#[ORM\Index(name: "specificity", columns: ['frequency'])]
#[ORM\Index(name: "is_deleted", columns: ['is_deleted'])]
#[ORM\Index(name: "is_dynamic", columns: ['is_dynamic'])]
#[ORM\Index(name: "serial", columns: ['serial'])]
#[ORM\Index(name: "nbcores", columns: ['nbcores'])]
#[ORM\Index(name: "nbthreads", columns: ['nbthreads'])]
#[ORM\Index(name: "entities_id", columns: ['entities_id'])]
#[ORM\Index(name: "is_recursive", columns: ['is_recursive'])]
#[ORM\Index(name: "busID", columns: ['busID'])]
#[ORM\Index(name: "item", columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: "otherserial", columns: ['otherserial'])]
#[ORM\Index(name: "locations_id", columns: ['locations_id'])]
#[ORM\Index(name: "states_id", columns: ['states_id'])]
class ItemDeviceProcessor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $itemsId;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 255, nullable: true)]
    private $itemtype;

    #[ORM\Column(name: 'deviceprocessors_id', type: 'integer', options: ['default' => 0])]
    private $deviceprocessorsId;

    #[ORM\Column(name: 'frequency', type: 'integer', options: ['default' => 0])]
    private $frequency;

    #[ORM\Column(name: 'serial', type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted;

    #[ORM\Column(name: 'is_dynamic', type: 'boolean', options: ['default' => 0])]
    private $isDynamic;

    #[ORM\Column(name: 'nbcores', type: 'integer', nullable: true)]
    private $nbcores;

    #[ORM\Column(name: 'nbthreads', type: 'integer', nullable: true)]
    private $nbthreads;

    #[ORM\Column(name: 'entities_id', type: 'integer', options: ['default' => 0])]
    private $entitiesId;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'bus_id', type: 'string', length: 255, nullable: true)]
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

    public function getDeviceprocessorsId(): ?int
    {
        return $this->deviceprocessorsId;
    }

    public function setDeviceprocessorsId(int $deviceprocessorsId): self
    {
        $this->deviceprocessorsId = $deviceprocessorsId;

        return $this;
    }

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function setFrequency(?int $frequency): self
    {
        $this->frequency = $frequency;

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
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->isDynamic;
    }

    public function setIsDynamic(?bool $isDynamic): self
    {
        $this->isDynamic = $isDynamic;

        return $this;
    }

    public function getNbCores(): ?int
    {
        return $this->nbcores;
    }

    public function setNbCores(?int $nbCores): self
    {
        $this->nbcores = $nbCores;

        return $this;
    }

    public function getNbThreads(): ?int
    {
        return $this->nbthreads;
    }

    public function setNbThreads(?int $nbThreads): self
    {
        $this->nbthreads = $nbThreads;

        return $this;
    }

    public function getEntitiesId(): ?array
    {
        return $this->entitiesId;
    }

    public function setEntitiesId(?array $entitiesId): self
    {
        $this->entitiesId = $entitiesId;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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

    public function getOtherSerial(): ?string
    {
        return $this->otherserial;
    }

    public function setOtherSerial(?string $otherserial): self
    {
        $this->otherserial = $otherserial;

        return $this;
    }

    public function getLocationsId(): ?array
    {
        return $this->locationsId;
    }

    public function setLocationsId(?array $locationsId): self
    {
        $this->locationsId = $locationsId;

        return $this;
    }

    public function getStatesId(): ?array
    {
        return $this->statesId;
    }

    public function setStatesId(?array $statesId): self
    {
        $this->statesId = $statesId;

        return $this;
    }
}
