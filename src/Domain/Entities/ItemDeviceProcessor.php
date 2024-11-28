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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $deviceprocessors_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $frequency;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_dynamic;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $nbcores;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $nbthreads;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
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

    public function getDeviceprocessorsId(): ?int
    {
        return $this->deviceprocessors_id;
    }

    public function setDeviceprocessorsId(int $deviceprocessors_id): self
    {
        $this->deviceprocessors_id = $deviceprocessors_id;

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
        return $this->is_deleted;
    }

    public function setIsDeleted(?bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(?bool $is_dynamic): self
    {
        $this->is_dynamic = $is_dynamic;

        return $this;
    }

    public function getNbCores(): ?int
    {
        return $this->nbcores;
    }

    public function setNbCores(?int $nb_cores): self
    {
        $this->nbcores = $nb_cores;

        return $this;
    }

    public function getNbThreads(): ?int
    {
        return $this->nbthreads;
    }

    public function setNbThreads(?int $nb_threads): self
    {
        $this->nbthreads = $nb_threads;

        return $this;
    }

    public function getEntitiesId(): ?array
    {
        return $this->entities_id;
    }

    public function setEntitiesId(?array $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(?bool $is_recursive): self
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
        return $this->locations_id;
    }

    public function setLocationsId(?array $locations_id): self
    {
        $this->locations_id = $locations_id;

        return $this;
    }

    public function getStatesId(): ?array
    {
        return $this->states_id;
    }

    public function setStatesId(?array $states_id): self
    {
        $this->states_id = $states_id;

        return $this;
    }
}
