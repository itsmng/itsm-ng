<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_reservationitems')]
#[ORM\Index(name: "is_active", columns: ["is_active"])]
#[ORM\Index(name: "itemtype_items_id", columns: ["itemtype", "items_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
class Reservationitem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_active;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

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

    public function getEntitiesId(): ?string
    {
        return $this->entities_id;
    }

    public function setEntitiesId(?string $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?string
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(?string $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getItemsId(): ?string
    {
        return $this->items_id;
    }

    public function setItemsId(?string $items_id): self
    {
        $this->items_id = $items_id;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getIsActive(): ?string
    {
        return $this->is_active;
    }

    public function setIsActive(?string $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getIsDeleted(): ?string
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(?string $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

}
