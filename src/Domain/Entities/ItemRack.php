<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_racks")]
#[ORM\UniqueConstraint(columns: ["itemtype", "items_id", "is_reserved"])]
#[ORM\Index(columns: ["racks_id", "itemtype", "items_id"])]
class ItemRack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer")]
    private $racks_id;

    #[ORM\Column(type: "string", length: 255)]
    private $itemtype;

    #[ORM\Column(type: "integer")]
    private $items_id;

    #[ORM\Column(type: "integer")]
    private $position;

    #[ORM\Column(type: "boolean", nullable: true)]
    private $orientation;

    #[ORM\Column(type: "string", length: 7, nullable: true)]
    private $bgcolor;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $hpos;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_reserved;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRacksId(): ?int
    {
        return $this->racks_id;
    }

    public function setRacksId(int $racks_id): self
    {
        $this->racks_id = $racks_id;

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
        return $this->is_reserved;
    }

    public function setIsReserved(int $is_reserved): self
    {
        $this->is_reserved = $is_reserved;

        return $this;
    }
}
