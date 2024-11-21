<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_appliances_items_relations')]
#[ORM\Index(name: 'appliances_items_id', columns: ['appliances_items_id'])]
#[ORM\Index(name: 'itemtype', columns: ['itemtype'])]
#[ORM\Index(name: 'items_id', columns: ['items_id'])]
#[ORM\Index(name: 'itemtype_items_id', columns: ['itemtype', 'items_id'])]
class ApplianceItemRelation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $appliances_items_id;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppliancesItemsId(): ?int
    {
        return $this->appliances_items_id;
    }

    public function setAppliancesItemsId(int $appliances_items_id): self
    {
        $this->appliances_items_id = $appliances_items_id;

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