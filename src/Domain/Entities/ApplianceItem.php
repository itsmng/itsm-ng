<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_appliances_items")]
#[ORM\UniqueConstraint(name:"appliances_id_items_id_itemtype", columns:["appliances_id", "items_id", "itemtype"])]
#[ORM\Index(name:"appliances_id", columns:["appliances_id"])]
#[ORM\Index(name:"itemtype_items_id", columns:["itemtype", "items_id"])]
class ApplianceItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", name: "appliances_id", options: ["default" => 0])]
    private $appliances_id;

    #[ORM\ManyToOne(targetEntity: Appliance::class, inversedBy: 'applianceitems')]
    #[ORM\JoinColumn(name: 'appliances_id', referencedColumnName: 'id', nullable: false)]
    private ?Appliance $appliance;

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

    /**
     * Get the value of appliance
     */
    public function getAppliance()
    {
        return $this->appliance;
    }

    /**
     * Set the value of appliance
     *
     * @return  self
     */
    public function setAppliance($appliance)
    {
        $this->appliance = $appliance;

        return $this;
    }
}
