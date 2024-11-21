<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_slalevels_tickets')]
#[ORM\UniqueConstraint(name: "tickets_id_slalevels_id", columns: ["tickets_id", "slalevels_id"])]
#[ORM\Index(name: "tickets_id", columns: ["tickets_id"])]
#[ORM\Index(name: "slalevels_id", columns: ["slalevels_id"])]
class SlalevelTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $tickets_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $slalevels_id;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicketsId(): ?int
    {
        return $this->tickets_id;
    }

    public function setTicketsId(int $tickets_id): self
    {
        $this->tickets_id = $tickets_id;

        return $this;
    }

    public function getSlalevelsId(): ?int
    {
        return $this->slalevels_id;
    }

    public function setSlalevelsId(int $slalevels_id): self
    {
        $this->slalevels_id = $slalevels_id;

        return $this;
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

}
