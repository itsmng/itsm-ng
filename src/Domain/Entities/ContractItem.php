<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_contract_items')]
#[ORM\UniqueConstraint(name: 'contracts_id_itemtype_items_id', columns: ['contracts_id', 'itemtype', 'items_id'])]
#[ORM\Index(name: 'items_id_itemtype', columns: ['items_id', 'itemtype'])]
#[ORM\Index(name: 'itemtype_items_id', columns: ['itemtype', 'items_id'])]

class ContractItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $contracts_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContractsId(): ?int
    {
        return $this->contracts_id;
    }

    public function setContractsId(int $contracts_id): self
    {
        $this->contracts_id = $contracts_id;

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

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(?string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
    }
}