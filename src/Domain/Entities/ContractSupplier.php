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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;


    #[ORM\ManyToOne(targetEntity: Supplier::class, inversedBy: 'contractSuppliers')]
    #[ORM\JoinColumn(name: 'suppliers_id', referencedColumnName: 'id', nullable: true)]
    private ?Supplier $supplier = null;


    #[ORM\ManyToOne(targetEntity: Contract::class, inversedBy: 'contractSuppliers')]
    #[ORM\JoinColumn(name: 'contracts_id', referencedColumnName: 'id', nullable: true)]
    private ?Contract $contract = null;

    public function getId(): ?int
    {
        return $this->id;
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
