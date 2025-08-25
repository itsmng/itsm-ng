<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_devicecases")]
#[ORM\Index(name: "items_id", columns: ["items_id"])]
#[ORM\Index(name: "devicecases_id", columns: ["devicecases_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "serial", columns: ["serial"])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id"])]
#[ORM\Index(name: "otherserial", columns: ["otherserial"])]
#[ORM\Index(name: "locations_id", columns: ["locations_id"])]
#[ORM\Index(name: "states_id", columns: ["states_id"])]
class ItemDeviceCase
{
    #[ORM\Id]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(name: "items_id", type: "integer", options: ["default" => 0])]
    private int $itemsId;

    #[ORM\Column(name: "itemtype", type: "string", length: 255, nullable: true)]
    private string $itemtype;

    #[ORM\ManyToOne(targetEntity: DeviceCase::class)]
    #[ORM\JoinColumn(name: 'devicecases_id', referencedColumnName: 'id', nullable: true)]
    private ?DeviceCase $devicecase = null;

    #[ORM\Column(name: "is_deleted", type: "boolean", options: ["default" => 0])]
    private bool $isDeleted;

    #[ORM\Column(name: "is_dynamic", type: "boolean", options: ["default" => 0])]
    private bool $isDynamic;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: "is_recursive", type: "boolean", options: ["default" => 0])]
    private bool $isRecursive;

    #[ORM\Column(name: "serial", type: "string", length: 255, nullable: true)]
    private string $serial;

    #[ORM\Column(name: "otherserial", type: "string", length: 255, nullable: true)]
    private string $otherserial;

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

    public function getIsDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getIsDynamic(): bool
    {
        return $this->isDynamic;
    }

    public function setIsDynamic(bool $isDynamic): self
    {
        $this->isDynamic = $isDynamic;

        return $this;
    }

    public function getIsRecursive(): bool
    {
        return $this->isRecursive;
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
     * Get the value of devicecase
     */
    public function getDevicecase()
    {
        return $this->devicecase;
    }

    /**
     * Set the value of devicecase
     *
     * @return  self
     */
    public function setDevicecase($devicecase)
    {
        $this->devicecase = $devicecase;

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
