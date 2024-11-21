<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

/*
Column	Type	Comment
id	int(11) Auto Increment
changes_id	int(11) [0]
itemtype	varchar(100) NULL
items_id	int(11) [0]
Indexes
PRIMARY	id
UNIQUE	changes_id, itemtype, items_id
INDEX	itemtype, items_id
 */

#[ORM\Entity]
#[ORM\Table(name: 'glpi_changes_items')]
#[ORM\UniqueConstraint(columns: ['changes_id', 'itemtype', 'items_id'])]
#[ORM\Index(columns: ['itemtype', 'items_id'])]
class ChangeItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $changes_id;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getChangesId(): int
    {
        return $this->changes_id;
    }

    public function setChangesId(int $changes_id): self
    {
        $this->changes_id = $changes_id;

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

    public function getItemsId(): int
    {
        return $this->items_id;
    }

    public function setItemsId(int $items_id): self
    {
        $this->items_id = $items_id;

        return $this;
    }
}