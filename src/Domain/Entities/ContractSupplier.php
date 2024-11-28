<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_contracts_suppliers')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['suppliers_id', 'contracts_id'])]
#[ORM\Index(name: 'contracts_id', columns: ['contracts_id'])]

class ContractSupplier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', name: 'suppliers_id', options: ['default' => 0])]
    private $suppliers_id;

    #[ORM\ManyToOne(targetEntity: Supplier::class, inversedBy: 'contractSuppliers')]
    #[ORM\JoinColumn(name: 'suppliers_id', referencedColumnName: 'id', nullable: false)]
    private ?Supplier $supplier;

    #[ORM\Column(type: 'integer', name: 'contracts_id', options: ['default' => 0])]
    private $contracts_id;

    #[ORM\ManyToOne(targetEntity: Contract::class, inversedBy: 'contractSuppliers')]
    #[ORM\JoinColumn(name: 'contracts_id', referencedColumnName: 'id', nullable: false)]
    private ?Contract $contract;

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

    /**
     * Get the value of supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * Set the value of supplier
     *
     * @return  self
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * Get the value of contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * Set the value of contract
     *
     * @return  self
     */
    public function setContract($contract)
    {
        $this->contract = $contract;

        return $this;
    }
}
