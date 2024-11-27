<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_ipaddresses")]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "textual", columns: ["name"])]
#[ORM\Index(name: "binary", columns: ["binary_0", "binary_1", "binary_2", "binary_3"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id", "is_deleted"])]
#[ORM\Index(name: "mainitem", columns: ["mainitemtype", "mainitems_id", "is_deleted"])]
class IpAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $items_id;

    #[ORM\Column(type: "string", length: 100)]
    private $itemtype;

    #[ORM\Column(type: "smallint", nullable: true, options: ["unsigned" => true, "default" => 0])]
    private $version;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $binary_0;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $binary_1;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $binary_2;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $binary_3;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_deleted;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_dynamic;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $mainitems_id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $mainitemtype;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(?int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getItemsId(): ?int
    {
        return $this->items_id;
    }

    public function setItemsId(?int $items_id): self
    {
        $this->items_id = $items_id;

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
        return $this->binary_0;
    }

    public function setBinary0(?int $binary_0): self
    {
        $this->binary_0 = $binary_0;

        return $this;
    }

    public function getBinary1(): ?int
    {
        return $this->binary_1;
    }

    public function setBinary1(?int $binary_1): self
    {
        $this->binary_1 = $binary_1;

        return $this;
    }

    public function getBinary2(): ?int
    {
        return $this->binary_2;
    }

    public function setBinary2(?int $binary_2): self
    {
        $this->binary_2 = $binary_2;

        return $this;
    }

    public function getBinary3(): ?int
    {
        return $this->binary_3;
    }

    public function setBinary3(?int $binary_3): self
    {
        $this->binary_3 = $binary_3;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(?bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(?bool $is_dynamic): self
    {
        $this->is_dynamic = $is_dynamic;

        return $this;
    }

    public function getMainitemsId(): ?int
    {
        return $this->mainitems_id;
    }

    public function setMainitemsId(?int $mainitems_id): self
    {
        $this->mainitems_id = $mainitems_id;

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
}
