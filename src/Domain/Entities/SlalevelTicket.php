<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_slalevels_tickets')]
#[ORM\UniqueConstraint(name: "unicity", columns: ["tickets_id", "slalevels_id"])]
#[ORM\Index(name: "tickets_id", columns: ["tickets_id"])]
#[ORM\Index(name: "slalevels_id", columns: ["slalevels_id"])]
class SlalevelTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'slalevelTickets')]
    #[ORM\JoinColumn(name: 'tickets_id', referencedColumnName: 'id', nullable: true)]
    private ?Ticket $ticket = null;

    #[ORM\ManyToOne(targetEntity: Slalevel::class, inversedBy: 'slalevelTickets')]
    #[ORM\JoinColumn(name: 'slalevels_id', referencedColumnName: 'id', nullable: true)]
    private ?Slalevel $slalevel = null;

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
     * Get the value of slalevel
     */
    public function getSlalevel()
    {
        return $this->slalevel;
    }

    /**
     * Set the value of slalevel
     *
     * @return  self
     */
    public function setSlalevel($slalevel)
    {
        $this->slalevel = $slalevel;

        return $this;
    }
}
