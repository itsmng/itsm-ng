<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "glpi_ipaddresses")]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "textual", columns: ["name"])]
#[ORM\Index(name: "binary", columns: ["binary_0", "binary_1", "binary_2", "binary_3"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id", "is_deleted"])]
#[ORM\Index(name: "mainitem", columns: ["mainitemtype", "mainitems_id", "is_deleted"])]
class IPAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'items_id', type: "integer", options: ["default" => 0])]
    private $itemsId;

    #[ORM\Column(name: 'itemtype', type: "string", length: 100)]
    private $itemtype;

    #[ORM\Column(name: 'version', type: "smallint", nullable: true, options: ["unsigned" => true, "default" => 0])]
    private $version;

    #[ORM\Column(name: 'name', type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'binary_0', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $binary0;

    #[ORM\Column(name: 'binary_1', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $binary1;

    #[ORM\Column(name: 'binary_2', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $binary2;

    #[ORM\Column(name: 'binary_3', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $binary3;

    #[ORM\Column(name: 'is_deleted', type: "boolean", options: ["default" => false])]
    private $isDeleted;

    #[ORM\Column(name: 'is_dynamic', type: "boolean", options: ["default" => false])]
    private $isDynamic;

    #[ORM\Column(name: 'mainitems_id', type: "integer", options: ["default" => 0])]
    private $mainitemsId;

    #[ORM\Column(name: 'mainitemtype', type: "string", length: 255, nullable: true)]
    private $mainitemtype;

    #[ORM\OneToMany(mappedBy: 'ipaddress', targetEntity: IpAddressIpNetwork::class)]
    private Collection $ipaddressIpnetworks;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->itemsId;
    }

    public function setItemsId(?int $itemsId): self
    {
        $this->itemsId = $itemsId;

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

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(?int $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBinary0(): ?int
    {
        return $this->binary0;
    }

    public function setBinary0(?int $binary0): self
    {
        $this->binary0 = $binary0;

        return $this;
    }

    public function getBinary1(): ?int
    {
        return $this->binary1;
    }

    public function setBinary1(?int $binary1): self
    {
        $this->binary1 = $binary1;

        return $this;
    }

    public function getBinary2(): ?int
    {
        return $this->binary2;
    }

    public function setBinary2(?int $binary2): self
    {
        $this->binary2 = $binary2;

        return $this;
    }

    public function getBinary3(): ?int
    {
        return $this->binary3;
    }

    public function setBinary3(?int $binary3): self
    {
        $this->binary3 = $binary3;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->isDynamic;
    }

    public function setIsDynamic(?bool $isDynamic): self
    {
        $this->isDynamic = $isDynamic;

        return $this;
    }

    public function getMainitemsId(): ?int
    {
        return $this->mainitemsId;
    }

    public function setMainitemsId(?int $mainitemsId): self
    {
        $this->mainitemsId = $mainitemsId;

        return $this;
    }

    public function getMainitemtype(): ?string
    {
        return $this->mainitemtype;
    }

    public function setMainitemtype(?string $mainitemtype): self
    {
        $this->mainitemtype = $mainitemtype;

        return $this;
    }

    /**
     * Get the value of entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the value of entity
     *
     * @return  self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }


    /**
     * Get the value of ipaddressIpnetworks
     */
    public function getIpaddressIpnetworks()
    {
        return $this->ipaddressIpnetworks;
    }

    /**
     * Set the value of ipaddressIpnetworks
     *
     * @return  self
     */
    public function setIpaddressIpnetworks($ipaddressIpnetworks)
    {
        $this->ipaddressIpnetworks = $ipaddressIpnetworks;

        return $this;
    }
}
