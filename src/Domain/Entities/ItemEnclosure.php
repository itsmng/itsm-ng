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
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer")]
    private $enclosures_id;

    #[ORM\Column(type: "string", length: 255)]
    private $itemtype;

    #[ORM\Column(type: "integer")]
    private $items_id;

    #[ORM\Column(type: "integer")]
    private $position;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnclosuresId(): ?int
    {
        return $this->enclosures_id;
    }

    public function setEnclosuresId(int $enclosures_id): self
    {
        $this->enclosures_id = $enclosures_id;

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
}
