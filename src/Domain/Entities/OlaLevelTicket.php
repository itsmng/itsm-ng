<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_olalevels_tickets')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['tickets_id', 'olalevels_id'])]
#[ORM\Index(name: 'tickets_id', columns: ['tickets_id'])]
#[ORM\Index(name: 'olalevels_id', columns: ['olalevels_id'])]
class OlaLevelTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'olalevelTickets')]
    #[ORM\JoinColumn(name: 'tickets_id', referencedColumnName: 'id', nullable: true)]
    private ?Ticket $ticket = null;

    #[ORM\ManyToOne(targetEntity: OlaLevel::class, inversedBy: 'olalevelTickets')]
    #[ORM\JoinColumn(name: 'olalevels_id', referencedColumnName: 'id', nullable: true)]
    private ?OlaLevel $olalevel = null;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
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
    public function getOlaLevel()
    {
        return $this->olalevel;
    }

    /**
     * Set the value of olalevel
     *
     * @return  self
     */
    public function setOlaLevel($olalevel)
    {
        $this->olalevel = $olalevel;

        return $this;
    }
}
