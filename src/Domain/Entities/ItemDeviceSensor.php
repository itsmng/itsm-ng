<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_devicesensors")]
#[ORM\Index(name: "items_id", columns: ["items_id"])]
#[ORM\Index(name: "devicesensors_id", columns: ["devicesensors_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "serial", columns: ["serial"])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id"])]
#[ORM\Index(name: "otherserial", columns: ["otherserial"])]
class ItemDeviceSensor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'items_id', type: "integer", options: ["default" => 0])]
    private $itemsId;

    #[ORM\Column(name: 'itemtype', type: "string", length: 255, nullable: true)]
    private $itemtype;

    #[ORM\ManyToOne(targetEntity: DeviceSensor::class)]
    #[ORM\JoinColumn(name: 'devicesensors_id', referencedColumnName: 'id', nullable: true)]
    private ?DeviceSensor $devicesensor = null;

    #[ORM\Column(name: 'is_deleted', type: "boolean", options: ["default" => 0])]
    private $isDeleted;

    #[ORM\Column(name: 'is_dynamic', type: "boolean", options: ["default" => 0])]
    private $isDynamic;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'serial', type: "string", length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(name: 'otherserial', type: "string", length: 255, nullable: true)]
    private $otherserial;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location = null;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state = null;

    public function getId()
    {
        return $this->id;
    }

    public function getItemsId()
    {
        return $this->itemsId;
    }

    public function setItemsId($itemsId)
    {
        $this->itemsId = $itemsId;
        return $this;
    }

    public function getItemtype()
    {
        return $this->itemtype;
    }

    public function setItemtype($itemtype)
    {
        $this->itemtype = $itemtype;
        return $this;
    }

    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;
        return $this;
    }

    public function getIsDynamic()
    {
        return $this->isDynamic;
    }

    public function setIsDynamic($isDynamic)
    {
        $this->isDynamic = $isDynamic;
        return $this;
    }


    public function getIsRecursive()
    {
        return $this->isRecursive;
    }

    public function setIsRecursive($isRecursive)
    {
        $this->isRecursive = $isRecursive;
        return $this;
    }

    public function getSerial()
    {
        return $this->serial;
    }

    public function setSerial($serial)
    {
        $this->serial = $serial;
        return $this;
    }

    public function getOtherserial()
    {
        return $this->otherserial;
    }

    public function setOtherserial($otherserial)
    {
        $this->otherserial = $otherserial;
        return $this;
    }

    /**
     * Get the value of devicesensor
     */ 
    public function getDevicesensor()
    {
        return $this->devicesensor;
    }

    /**
     * Set the value of devicesensor
     *
     * @return  self
     */ 
    public function setDevicesensor($devicesensor)
    {
        $this->devicesensor = $devicesensor;

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
