<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_appliances_items")]
#[ORM\UniqueConstraint(name:"unicity", columns:["appliances_id", "items_id", "itemtype"])]
#[ORM\Index(name:"appliances_id", columns:["appliances_id"])]
#[ORM\Index(name:"item", columns:["itemtype", "items_id"])]
class ApplianceItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Appliance::class)]
    #[ORM\JoinColumn(name: 'appliances_id', referencedColumnName: 'id', nullable: true)]
    private ?Appliance $appliance = null;

    #[ORM\Column(name: 'items_id', type: "integer", options: ["default" => 0])]
    private $itemsId;

    #[ORM\Column(name: 'itemtype', type: "string", length: 100, options: ["default" => ""])]
    private $itemtype;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->itemsId;
    }

    public function setItemsId(?int $itemsId): self
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
