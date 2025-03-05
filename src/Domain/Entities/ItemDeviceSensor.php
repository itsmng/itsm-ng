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

    #[ORM\Column(name: 'devicesensors_id', type: "integer", options: ["default" => 0])]
    private $devicesensorsId;

    #[ORM\Column(name: 'is_deleted', type: "boolean", options: ["default" => 0])]
    private $isDeleted;

    #[ORM\Column(name: 'is_dynamic', type: "boolean", options: ["default" => 0])]
    private $isDynamic;

    #[ORM\Column(name: 'entities_id', type: "integer", options: ["default" => 0])]
    private $entitiesId;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'serial', type: "string", length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(name: 'otherserial', type: "string", length: 255, nullable: true)]
    private $otherserial;

    #[ORM\Column(name: 'locations_id', type: "integer", options: ["default" => 0])]
    private $locationsId;

    #[ORM\Column(name: 'states_id', type: "integer", options: ["default" => 0])]
    private $statesId;

    public function getId()
    {
        return $this->id;
    }

    public function getItemsId()
    {
        return $this->itemsId;
    }

    public function setItemsId($items_id)
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

    public function getDevicesensorsId()
    {
        return $this->devicesensorsId;
    }

    public function setDevicesensorsId($devicesensors_id)
    {
        $this->devicesensorsId = $devicesensorsId;
        return $this;
    }

    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    public function setIsDeleted($is_deleted)
    {
        $this->isDeleted = $isDeleted;
        return $this;
    }

    public function getIsDynamic()
    {
        return $this->isDynamic;
    }

    public function setIsDynamic($is_dynamic)
    {
        $this->isDynamic = $isDynamic;
        return $this;
    }

    public function getEntitiesId()
    {
        return $this->entitiesId;
    }

    public function setEntitiesId($entities_id)
    {
        $this->entitiesId = $entitiesId;
        return $this;
    }

    public function getIsRecursive()
    {
        return $this->isRecursive;
    }

    public function setIsRecursive($is_recursive)
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

    public function getLocationsId()
    {
        return $this->locationsId;
    }

    public function setLocationsId($locations_id)
    {
        $this->locationsId = $locationsId;
        return $this;
    }

    public function getStatesId()
    {
        return $this->statesId;
    }

    public function setStatesId($states_id)
    {
        $this->statesId = $statesId;
        return $this;
    }
}
