<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
//Table: glpi_knowbaseitems_items
//
//Select data Show structure Alter table New item
//Column	Type	Comment
//id	int(11) Auto Increment
//knowbaseitems_id	int(11)
//itemtype	varchar(100)
//items_id	int(11) [0]
//date_creation	timestamp NULL
//date_mod	timestamp NULL
//Indexes
//PRIMARY	id
//UNIQUE	itemtype, items_id, knowbaseitems_id
//INDEX	itemtype
//INDEX	items_id
//INDEX	itemtype, items_id

#[ORM\Entity]
#[ORM\Table(name: "glpi_knowbaseitems_items")]
#[ORM\UniqueConstraint(columns: ["itemtype", "items_id", "knowbaseitems_id"])]
#[ORM\Index(columns: ["itemtype"])]
#[ORM\Index(columns: ["items_id"])]
#[ORM\Index(columns: ["itemtype", "items_id"])]
class KnowbaseitemItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer")]
    private $knowbaseitems_id;

    #[ORM\Column(type: "string", length: 100)]
    private $itemtype;

    #[ORM\Column(type: "integer", options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKnowbaseitemsId(): ?int
    {
        return $this->knowbaseitems_id;
    }

    public function setKnowbaseitemsId(int $knowbaseitems_id): self
    {
        $this->knowbaseitems_id = $knowbaseitems_id;

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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }
}
