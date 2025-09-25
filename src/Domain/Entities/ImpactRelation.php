<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_impactrelations")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["itemtype_source", "items_id_source", "itemtype_impacted", "items_id_impacted"])]
#[ORM\Index(name: "source_asset", columns: ["itemtype_source", "items_id_source"])]
#[ORM\Index(name: "impacted_asset", columns: ["itemtype_impacted", "items_id_impacted"])]
class ImpactRelation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'itemtype_source', type: "string", length: 255, options: ["default" => ""])]
    private $itemtypeSource = '';

    #[ORM\Column(name: 'items_id_source', type: "integer", options: ["default" => 0])]
    private $itemsIdSource = 0;

    #[ORM\Column(name: 'itemtype_impacted', type: "string", length: 255, options: ["default" => ""])]
    private $itemtypeImpacted = '';

    #[ORM\Column(name: 'items_id_impacted', type: "integer", options: ["default" => 0])]
    private $itemsIdImpacted = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemtypeSource(): ?string
    {
        return $this->itemtypeSource;
    }

    public function setItemtypeSource(string $itemtypeSource): self
    {
        $this->itemtypeSource = $itemtypeSource;

        return $this;
    }

    public function getItemsIdSource(): ?int
    {
        return $this->itemsIdSource;
    }

    public function setItemsIdSource(int $itemsIdSource): self
    {
        $this->itemsIdSource = $itemsIdSource;

        return $this;
    }

    public function getItemtypeImpacted(): ?string
    {
        return $this->itemtypeImpacted;
    }

    public function setItemtypeImpacted(string $itemtypeImpacted): self
    {
        $this->itemtypeImpacted = $itemtypeImpacted;

        return $this;
    }

    public function getItemsIdImpacted(): ?int
    {
        return $this->itemsIdImpacted;
    }

    public function setItemsIdImpacted(int $itemsIdImpacted): self
    {
        $this->itemsIdImpacted = $itemsIdImpacted;

        return $this;
    }
}
