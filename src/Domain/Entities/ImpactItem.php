<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_impactitems")]
#[ORM\UniqueConstraint(name: "unicity", columns:["itemtype", "items_id"])]
#[ORM\Index(name: "item", columns:["itemtype", "items_id"])]
#[ORM\Index(name: "parent_id", columns:["parent_id"])]
#[ORM\Index(name: "impactcontexts_id", columns:["impactcontexts_id"])]
class ImpactItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'itemtype', type: "string", length: 255, options: ["default" => ""])]
    private $itemtype = "";

    #[ORM\Column(name: 'items_id', type: "integer", options: ["default" => 0])]
    private $items_id = 0;

    #[ORM\Column(name: 'parent_id', type: "integer", options: ["default" => 0])]
    private $parentId = 0;

    #[ORM\Column(name: 'impactcontexts_id', type: "integer", options: ["default" => 0])]
    private $impactcontextsId = 0;

    #[ORM\Column(name: 'is_slave', type: "boolean", options: ["default" => true])]
    private $isSlave = true;

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

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(int $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getImpactcontextsId(): ?int
    {
        return $this->impactcontextsId;
    }

    public function setImpactcontextsId(int $impactcontextsId): self
    {
        $this->impactcontextsId = $impactcontextsId;

        return $this;
    }

    public function getIsSlave(): ?bool
    {
        return $this->isSlave;
    }

    public function setIsSlave(bool $isSlave): self
    {
        $this->isSlave = $isSlave;

        return $this;
    }
}
