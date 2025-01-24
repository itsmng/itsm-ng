<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_knowbaseitems_items")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["itemtype", "items_id", "knowbaseitems_id"])]
#[ORM\Index(name: "itemtype", columns: ["itemtype"])]
#[ORM\Index(name: "item_id", columns: ["items_id"])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id"])]
class KnowbaseitemItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'knowbaseitems_id', type: "integer")]
    private $knowbaseitemsId;

    #[ORM\Column(name: 'itemtype', type: "string", length: 100)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: "integer", options: ['default' => 0])]
    private $itemsId;

    #[ORM\Column(name: 'date_creation', type: "datetime", nullable: true)]
    private $dateCreation;

    #[ORM\Column(name: 'date_mod', type: "datetime", nullable: true)]
    private $dateMod;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKnowbaseitemsId(): ?int
    {
        return $this->knowbaseitemsId;
    }

    public function setKnowbaseitemsId(int $knowbaseitemsId): self
    {
        $this->knowbaseitemsId = $knowbaseitemsId;

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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }
}
