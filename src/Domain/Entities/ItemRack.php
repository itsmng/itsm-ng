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

    #[ORM\Column(name: 'racks_id', type: "integer")]
    private $racksId;

    #[ORM\Column(name: 'itemtype', type: "string", length: 255)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: "integer")]
    private $itemsId;

    #[ORM\Column(name: 'position', type: "integer")]
    private $position;

    #[ORM\Column(name: 'orientation', type: "boolean", nullable: true)]
    private $orientation;

    #[ORM\Column(name: 'bgcolor', type: "string", length: 7, nullable: true)]
    private $bgcolor;

    #[ORM\Column(name: 'hpos', type: "boolean", options: ["default" => 0])]
    private $hpos;

    #[ORM\Column(name: 'is_reserved', type: "boolean", options: ["default" => 0])]
    private $isReserved;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRacksId(): ?int
    {
        return $this->racksId;
    }

    public function setRacksId(int $racksId): self
    {
        $this->racksId = $racksId;

        return $this;
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
        return $this->itemsId;
    }

    public function setItemsId(int $itemsId): self
    {
        $this->itemsId = $itemsId;

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
}
