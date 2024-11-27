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
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $items_id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $itemtype;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $devicesensors_id;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_deleted;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_dynamic;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_recursive;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $otherserial;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $locations_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $states_id;

    public function getId()
    {
        return $this->id;
    }

    public function getItems_id()
    {
        return $this->items_id;
    }

    public function setItems_id($items_id)
    {
        $this->items_id = $items_id;
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

    public function getDevicesensors_id()
    {
        return $this->devicesensors_id;
    }

    public function setDevicesensors_id($devicesensors_id)
    {
        $this->devicesensors_id = $devicesensors_id;
        return $this;
    }

    public function getIs_deleted()
    {
        return $this->is_deleted;
    }

    public function setIs_deleted($is_deleted)
    {
        $this->is_deleted = $is_deleted;
        return $this;
    }

    public function getIs_dynamic()
    {
        return $this->is_dynamic;
    }

    public function setIs_dynamic($is_dynamic)
    {
        $this->is_dynamic = $is_dynamic;
        return $this;
    }

    public function getEntities_id()
    {
        return $this->entities_id;
    }

    public function setEntities_id($entities_id)
    {
        $this->entities_id = $entities_id;
        return $this;
    }

    public function getIs_recursive()
    {
        return $this->is_recursive;
    }

    public function setIs_recursive($is_recursive)
    {
        $this->is_recursive = $is_recursive;
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

    public function getLocations_id()
    {
        return $this->locations_id;
    }

    public function setLocations_id($locations_id)
    {
        $this->locations_id = $locations_id;
        return $this;
    }

    public function getStates_id()
    {
        return $this->states_id;
    }

    public function setStates_id($states_id)
    {
        $this->states_id = $states_id;
        return $this;
    }
}
