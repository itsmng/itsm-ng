<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_changes_suppliers')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['changes_id', 'type', 'suppliers_id'])]
#[ORM\Index(name: 'group', columns: ['suppliers_id', 'type'])]

class ChangeSupplier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', name: 'changes_id', options: ['default' => 0])]
    private $changes_id;

    #[ORM\ManyToOne(targetEntity: Change::class, inversedBy: 'changesSuppliers')]
    #[ORM\JoinColumn(name: 'changes_id', referencedColumnName: 'id', nullable: false)]
    private ?Change $change;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $suppliers_id;

    #[ORM\ManyToOne(targetEntity: Supplier::class, inversedBy: 'changesSuppliers')]
    #[ORM\JoinColumn(name: 'supplier_id', referencedColumnName: 'id', nullable: false)]
    private ?Supplier $supplier;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $type;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $use_notification;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $alternative_email;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChangesId(): ?int
    {
        return $this->changes_id;
    }

    public function setChangesId(int $changes_id): self
    {
        $this->changes_id = $changes_id;

        return $this;
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUseNotification(): ?bool
    {
        return $this->use_notification;
    }

    public function setUseNotification(bool $use_notification): self
    {
        $this->use_notification = $use_notification;

        return $this;
    }

    public function getAlternativeEmail(): ?string
    {
        return $this->alternative_email;
    }

    public function setAlternativeEmail(?string $alternative_email): self
    {
        $this->alternative_email = $alternative_email;

        return $this;
    }

    /**
     * Get the value of change
     */
    public function getChange()
    {
        return $this->change;
    }

    /**
     * Set the value of change
     *
     * @return  self
     */
    public function setChange($change)
    {
        $this->change = $change;

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
}
