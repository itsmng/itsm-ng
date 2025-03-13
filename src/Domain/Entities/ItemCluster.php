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
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Cluster::class)]
    #[ORM\JoinColumn(name: 'clusters_id', referencedColumnName: 'id', nullable: true)]
    private ?Cluster $cluster = null;


    #[ORM\Column(name: 'itemtype', type: "string", length: 100, nullable: true)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: "integer", options: ["default" => 0])]
    private $itemsId;

    public function getId(): ?int
    {
        return $this->id;
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
        return $this->itemsId;
    }

    public function setItemsId(int $itemsId): self
    {
        $this->itemsId = $itemsId;

        return $this;
    }

    /**
     * Get the value of cluster
     */ 
    public function getCluster()
    {
        return $this->cluster;
    }

    /**
     * Set the value of cluster
     *
     * @return  self
     */ 
    public function setCluster($cluster)
    {
        $this->cluster = $cluster;

        return $this;
    }
}
