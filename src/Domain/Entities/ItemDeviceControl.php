<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_devicecontrols")]
#[ORM\Index(name: "items_id", columns: ["items_id"])]
#[ORM\Index(name: "devicecontrols_id", columns: ["devicecontrols_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "serial", columns: ["serial"])]
#[ORM\Index(name: "busID", columns: ["busID"])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id"])]
#[ORM\Index(name: "otherserial", columns: ["otherserial"])]
#[ORM\Index(name: "locations_id", columns: ["locations_id"])]
#[ORM\Index(name: "states_id", columns: ["states_id"])]
class ItemDeviceControl
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'items_id', type: "integer", options: ["default" => 0])]
    private $items_id = 0;

    #[ORM\Column(name: 'itemtype', type: "string", length: 255, nullable: true)]
    private $itemtype;

    #[ORM\ManyToOne(targetEntity: DeviceControl::class)]
    #[ORM\JoinColumn(name: 'devicecontrols_id', referencedColumnName: 'id', nullable: true)]
    private ?DeviceControl $devicecontrol = null;

    #[ORM\Column(name: 'is_deleted', type: "boolean", options: ["default" => 0])]
    private $isDeleted = false;

    #[ORM\Column(name: 'is_dynamic', type: "boolean", options: ["default" => 0])]
    private $isDynamic = false;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => 0])]
    private $isRecursive = false;

    #[ORM\Column(name: 'serial', type: "string", length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(name: 'busID', type: "string", length: 255, nullable: true)]
    private $busID;

    #[ORM\Column(name: 'otherserial', type: "string", length: 255, nullable: true)]
    private $otherserial;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location = null;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state = null;


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

    public function getIsDeleted(): bool
    {
        return (bool) $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getIsDynamic(): bool
    {
        return (bool) $this->isDynamic;
    }

    public function setIsDynamic(bool $isDynamic): self
    {
        $this->isDynamic = $isDynamic;

        return $this;
    }

    public function getIsRecursive(): bool
    {
        return (bool) $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
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

    /**
     * Get the value of devicecontrol
     */
    public function getDevicecontrol()
    {
        return $this->devicecontrol;
    }

    /**
     * Set the value of devicecontrol
     *
     * @return  self
     */
    public function setDevicecontrol($devicecontrol)
    {
        $this->devicecontrol = $devicecontrol;

        return $this;
    }

    /**
     * Get the value of entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the value of entity
     *
     * @return  self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get the value of location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the value of location
     *
     * @return  self
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get the value of state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set the value of state
     *
     * @return  self
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }
}
