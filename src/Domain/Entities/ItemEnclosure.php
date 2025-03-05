<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_enclosures")]
#[ORM\UniqueConstraint(name: "item", columns: ["itemtype", "items_id"])]
#[ORM\Index(name: "relation", columns: ["enclosures_id", "itemtype", "items_id"])]
class ItemEnclosure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'enclosures_id', type: "integer")]
    private $enclosuresId;

    #[ORM\Column(name: 'itemtype', type: "string", length: 255)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: "integer")]
    private $itemsId;

    #[ORM\Column(name: 'position', type: "integer")]
    private $position;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnclosuresId(): ?int
    {
        return $this->enclosuresId;
    }

    public function setEnclosuresId(int $enclosuresId): self
    {
        $this->enclosuresId = $enclosuresId;

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
}
