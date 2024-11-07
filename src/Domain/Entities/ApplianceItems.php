<?php

// Column	Type	Comment
//id	int(11) Auto Increment
//appliances_id	int(11) [0]
//items_id	int(11) [0]
//itemtype	varchar(100) []
//Indexes
//PRIMARY	id
//UNIQUE	appliances_id, items_id, itemtype
//INDEX	appliances_id
//INDEX	itemtype, items_id

namespace Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_appliances_items")]
#[ORM\UniqueConstraint(name:"appliances_id_items_id_itemtype", columns:["appliances_id", "items_id", "itemtype"])]
#[ORM\Index(name:"appliances_id", columns:["appliances_id"])]
#[ORM\Index(name:"itemtype_items_id", columns:["itemtype", "items_id"])]
class ApplianceItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $appliances_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $items_id;

    #[ORM\Column(type: "string", length: 100, options: ["default" => ""])]
    private $itemtype;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppliancesId(): ?int
    {
        return $this->appliances_id;
    }

    public function setAppliancesId(?int $appliances_id): self
    {
        $this->appliances_id = $appliances_id;

        return $this;
    }

    public function getItemsId(): ?int
    {
        return $this->items_id;
    }

    public function setItemsId(?int $items_id): self
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
}
