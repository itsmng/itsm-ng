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
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, options: ["default" => ""])]
    private $itemtype;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $items_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $parent_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $impactcontexts_id;

    #[ORM\Column(type: "boolean", options: ["default" => true])]
    private $is_slave;

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
        return $this->parent_id;
    }

    public function setParentId(int $parent_id): self
    {
        $this->parent_id = $parent_id;

        return $this;
    }

    public function getImpactcontextsId(): ?int
    {
        return $this->impactcontexts_id;
    }

    public function setImpactcontextsId(int $impactcontexts_id): self
    {
        $this->impactcontexts_id = $impactcontexts_id;

        return $this;
    }

    public function getIsSlave(): ?bool
    {
        return $this->is_slave;
    }

    public function setIsSlave(bool $is_slave): self
    {
        $this->is_slave = $is_slave;

        return $this;
    }
}
