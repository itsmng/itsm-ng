<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_problems_suppliers')]
#[ORM\UniqueConstraint(name: "unicity", columns: ["problems_id", "type", "suppliers_id"])]
#[ORM\Index(name: "group", columns: ["suppliers_id", "type"])]
class ProblemSupplier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Problem::class, inversedBy: 'problemSuppliers')]
    #[ORM\JoinColumn(name: 'problems_id', referencedColumnName: 'id', nullable: true)]
    private ?Problem $problem = null;

    #[ORM\ManyToOne(targetEntity: Supplier::class, inversedBy: 'problemSuppliers')]
    #[ORM\JoinColumn(name: 'suppliers_id', referencedColumnName: 'id', nullable: true)]
    private ?Supplier $supplier = null;

    #[ORM\Column(name: 'type', type: 'integer', options: ['default' => 1])]
    private $type;

    #[ORM\Column(name: 'use_notification', type: 'boolean', options: ['default' => 0])]
    private $useNotification;

    #[ORM\Column(name: 'alternative_email', type: 'string', length: 255, nullable: true)]
    private $alternativeEmail;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }


    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUseNotification(): ?bool
    {
        return $this->useNotification;
    }


    public function setUseNotification(?bool $useNotification): self
    {
        $this->useNotification = $useNotification;

        return $this;
    }

    public function getAlternativeEmail(): ?string
    {
        return $this->alternativeEmail;
    }


    public function setAlternativeEmail(?string $alternativeEmail): self
    {
        $this->alternativeEmail = $alternativeEmail;

        return $this;
    }


    /**
     * Get the value of problem
     */
    public function getProblem()
    {
        return $this->problem;
    }

    /**
     * Set the value of problem
     *
     * @return  self
     */
    public function setProblem($problem)
    {
        $this->problem = $problem;

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
