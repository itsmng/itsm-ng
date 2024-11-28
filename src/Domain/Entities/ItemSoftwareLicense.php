<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_softwarelicenses")]
#[ORM\Index(name: "items_id", columns: ["items_id"])]
#[ORM\Index(name: "itemtype", columns: ["itemtype"])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id"])]
#[ORM\Index(name: "softwarelicenses_id", columns: ["softwarelicenses_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
class ItemSoftwareLicense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $items_id;

    #[ORM\Column(type: "string", length: 100)]
    private $itemtype;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $softwarelicenses_id;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_deleted;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_dynamic;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
    }

    public function getSoftwarelicensesId(): ?int
    {
        return $this->softwarelicenses_id;
    }

    public function setSoftwarelicensesId(int $softwarelicenses_id): self
    {
        $this->softwarelicenses_id = $softwarelicenses_id;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(bool $is_dynamic): self
    {
        $this->is_dynamic = $is_dynamic;

        return $this;
    }
}
