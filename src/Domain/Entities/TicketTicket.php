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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'tickets_id1', type: 'integer', options: ['default' => 0])]
    private $ticketsId1;

    #[ORM\Column(name: 'tickets_id2', type: 'integer', options: ['default' => 0])]
    private $ticketsId2;

    #[ORM\Column(name: 'link', type: 'integer', options: ['default' => 1])]
    private $link;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicketsId1(): ?int
    {
        return $this->ticketsId1;
    }

    public function setTicketsId1(?int $ticketsId1): self
    {
        $this->ticketsId1 = $tickets_id_1;

        return $this;
    }

    public function getTicketsId2(): ?int
    {
        return $this->ticketsId2;
    }

    public function setTicketsId2(?int $ticketsId2): self
    {
        $this->ticketsId2 = $tickets_id_2;

        return $this;
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

}
