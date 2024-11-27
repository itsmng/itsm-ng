<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_changes_tickets')]
#[ORM\UniqueConstraint(columns: ['changes_id', 'tickets_id'])]
#[ORM\Index(columns: ['tickets_id'])]
class ChangeTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', name: 'changes_id', options: ['default' => 0])]
    private $changes_id;

    #[ORM\ManyToOne(targetEntity: Change::class, inversedBy: 'changesTickets')]
    #[ORM\JoinColumn(name: 'changes_id', referencedColumnName: 'id', nullable: false)]
    private ?Change $change;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $tickets_id;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'changesTickets')]
    #[ORM\JoinColumn(name: 'tickets_id', referencedColumnName: 'id', nullable: false)]
    private ?Ticket $ticket;

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

    public function getTicketsId(): int
    {
        return $this->tickets_id;
    }

    public function setTicketsId(int $problems_id): self
    {
        $this->tickets_id = $problems_id;

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
}
