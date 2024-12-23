<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_tickets_tickets")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["tickets_id_1", "tickets_id_2"])]
class TicketTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'ticketTickets1')]
    #[ORM\JoinColumn(name: 'tickets_id_1', referencedColumnName: 'id', nullable: true)]
    private ?Ticket $ticket1;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'ticketTickets2')]
    #[ORM\JoinColumn(name: 'tickets_id_2', referencedColumnName: 'id', nullable: true)]
    private ?Ticket $ticket2;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $link;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLink(): ?int
    {
        return $this->link;
    }

    public function setLink(?int $link): self
    {
        $this->link = $link;

        return $this;
    }


    /**
     * Get the value of ticket1
     */ 
    public function getTicket1()
    {
        return $this->ticket1;
    }

    /**
     * Set the value of ticket1
     *
     * @return  self
     */ 
    public function setTicket1($ticket1)
    {
        $this->ticket1 = $ticket1;

        return $this;
    }

    /**
     * Get the value of ticket2
     */ 
    public function getTicket2()
    {
        return $this->ticket2;
    }

    /**
     * Set the value of ticket2
     *
     * @return  self
     */ 
    public function setTicket2($ticket2)
    {
        $this->ticket2 = $ticket2;

        return $this;
    }
}
