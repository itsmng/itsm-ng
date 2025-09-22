<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_racks")]
#[ORM\UniqueConstraint(name: 'item', columns: ["itemtype", "items_id", "is_reserved"])]
#[ORM\Index(name: 'relation', columns: ["racks_id", "itemtype", "items_id"])]
class ItemRack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Rack::class)]
    #[ORM\JoinColumn(name: 'racks_id', referencedColumnName: 'id', nullable: true)]
    private ?Rack $rack = null;

    #[ORM\Column(name: 'itemtype', type: "string", length: 255)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: "integer")]
    private $items_id;

    #[ORM\Column(name: 'position', type: "integer")]
    private $position;

    #[ORM\Column(name: 'orientation', type: "boolean", nullable: true)]
    private $orientation;

    #[ORM\Column(name: 'bgcolor', type: "string", length: 7, nullable: true)]
    private $bgcolor;

    #[ORM\Column(name: 'hpos', type: "boolean", options: ["default" => 0])]
    private $hpos = 0;

    #[ORM\Column(name: 'is_reserved', type: "boolean", options: ["default" => 0])]
    private $isReserved = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getOrientation(): ?int
    {
        return $this->orientation;
    }

    public function setOrientation(int $orientation): self
    {
        $this->orientation = $orientation;

        return $this;
    }

    public function getBgcolor(): ?string
    {
        return $this->bgcolor;
    }

    public function setBgcolor(?string $bgcolor): self
    {
        $this->bgcolor = $bgcolor;

        return $this;
    }

    public function getHpos(): ?int
    {
        return $this->hpos;
    }

    public function setHpos(int $hpos): self
    {
        $this->hpos = $hpos;

        return $this;
    }

    public function getIsReserved(): ?int
    {
        return $this->isReserved;
    }

    public function setIsReserved(int $isReserved): self
    {
        $this->isReserved = $isReserved;

        return $this;
    }

    /**
     * Get the value of rack
     */
    public function getRack()
    {
        return $this->rack;
    }

    /**
     * Set the value of rack
     *
     * @return  self
     */
    public function setRack($rack)
    {
        $this->rack = $rack;

        return $this;
    }
}
