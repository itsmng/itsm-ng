<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_impactrelations")]
#[ORM\UniqueConstraint(columns: ["itemtype_source", "items_id_source", "itemtype_impacted", "items_id_impacted"])]
#[ORM\Index(columns: ["itemtype_source", "items_id_source"])]
#[ORM\Index(columns: ["itemtype_impacted", "items_id_impacted"])]
class ImpactRelation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, options: ["default" => ""])]
    private $itemtype_source;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $items_id_source;

    #[ORM\Column(type: "string", length: 255, options: ["default" => ""])]
    private $itemtype_impacted;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $items_id_impacted;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemtypeSource(): ?string
    {
        return $this->itemtype_source;
    }

    public function setItemtypeSource(string $itemtype_source): self
    {
        $this->itemtype_source = $itemtype_source;

        return $this;
    }

    public function getItemsIdSource(): ?int
    {
        return $this->items_id_source;
    }

    public function setItemsIdSource(int $items_id_source): self
    {
        $this->items_id_source = $items_id_source;

        return $this;
    }

    public function getItemtypeImpacted(): ?string
    {
        return $this->itemtype_impacted;
    }

    public function setItemtypeImpacted(string $itemtype_impacted): self
    {
        $this->itemtype_impacted = $itemtype_impacted;

        return $this;
    }

    public function getItemsIdImpacted(): ?int
    {
        return $this->items_id_impacted;
    }

    public function setItemsIdImpacted(int $items_id_impacted): self
    {
        $this->items_id_impacted = $items_id_impacted;

        return $this;
    }
}
