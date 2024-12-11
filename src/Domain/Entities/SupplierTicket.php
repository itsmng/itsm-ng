<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_suppliers_tickets")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["tickets_id", "type", "suppliers_id"])]
#[ORM\Index(name: "group", columns: ["suppliers_id", "type"])]
class SupplierTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'supplierTickets')]
    #[ORM\JoinColumn(name: 'tickets_id', referencedColumnName: 'id', nullable: true)]
    private ?Ticket $ticket;

    #[ORM\ManyToOne(targetEntity: Supplier::class, inversedBy: 'supplierTickets')]
    #[ORM\JoinColumn(name: 'suppliers_id', referencedColumnName: 'id', nullable: true)]
    private ?Supplier $supplier;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $type;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $use_notification;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $alternative_email;

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
        return $this->use_notification;
    }

    public function setUseNotification(?bool $use_notification): self
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
     * Get the value of ticket
     */ 
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * Set the value of ticket
     *
     * @return  self
     */ 
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;

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
