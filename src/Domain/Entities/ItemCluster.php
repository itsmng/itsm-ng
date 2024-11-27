<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_clusters")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["clusters_id", "itemtype", "items_id"])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id"])]
class ItemCluster
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $clusters_id;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private $itemtype;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $items_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClustersId(): ?int
    {
        return $this->clusters_id;
    }

    public function setClustersId(int $clusters_id): self
    {
        $this->clusters_id = $clusters_id;

        return $this;
    }

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(?string $itemtype): self
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
}
