<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_tickets_tickets")]
#[ORM\UniqueConstraint(name: "tickets_id_1_tickets_id_2", columns: ["tickets_id_1", "tickets_id_2"])]
class TicketTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $tickets_id_1;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $tickets_id_2;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $link;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicketsId1(): ?int
    {
        return $this->tickets_id_1;
    }

    public function setTicketsId1(?int $tickets_id_1): self
    {
        $this->tickets_id_1 = $tickets_id_1;

        return $this;
    }

    public function getTicketsId2(): ?int
    {
        return $this->tickets_id_2;
    }

    public function setTicketsId2(?int $tickets_id_2): self
    {
        $this->tickets_id_2 = $tickets_id_2;

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
