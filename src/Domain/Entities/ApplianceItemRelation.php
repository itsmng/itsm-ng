<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_appliances_items_relations')]
#[ORM\Index(name: 'appliances_items_id', columns: ['appliances_items_id'])]
#[ORM\Index(name: 'itemtype', columns: ['itemtype'])]
#[ORM\Index(name: 'items_id', columns: ['items_id'])]
#[ORM\Index(name: 'item', columns: ['itemtype', 'items_id'])]
class ApplianceItemRelation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'appliances_items_id', type: 'integer', options: ['default' => 0])]
    private $appliancesItemsId = 0;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $items_id = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppliancesItemsId(): ?int
    {
        return $this->appliancesItemsId;
    }

    public function setAppliancesItemsId(int $appliancesItemsId): self
    {
        $this->appliancesItemsId = $appliancesItemsId;

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
