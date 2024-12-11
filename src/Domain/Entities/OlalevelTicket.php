<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_olalevels_tickets')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['tickets_id', 'olalevels_id'])]
#[ORM\Index(name: 'tickets_id', columns: ['tickets_id'])]
#[ORM\Index(name: 'olalevels_id', columns: ['olalevels_id'])]
class OlalevelTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'olalevelTickets')]
    #[ORM\JoinColumn(name: 'tickets_id', referencedColumnName: 'id', nullable: true)]
    private ?Ticket $ticket;

    #[ORM\ManyToOne(targetEntity: Olalevel::class, inversedBy: 'olalevelTickets')]
    #[ORM\JoinColumn(name: 'olalevels_id', referencedColumnName: 'id', nullable: true)]
    private ?Olalevel $olalevel;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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
     * Get the value of olalevel
     */ 
    public function getOlalevel()
    {
        return $this->olalevel;
    }

    /**
     * Set the value of olalevel
     *
     * @return  self
     */ 
    public function setOlalevel($olalevel)
    {
        $this->olalevel = $olalevel;

        return $this;
    }
}
