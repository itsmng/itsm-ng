<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_contract_suppliers')]
#[ORM\UniqueConstraint(name: 'suppliers_id_contracts_id', columns: ['suppliers_id', 'contracts_id'])]
#[ORM\Index(name: 'contracts_id', columns: ['contracts_id'])]

class ContractSupplier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $suppliers_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $contracts_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSuppliersId(): ?int
    {
        return $this->suppliers_id;
    }

    public function setSuppliersId(int $suppliers_id): self
    {
        $this->suppliers_id = $suppliers_id;

        return $this;
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
}   