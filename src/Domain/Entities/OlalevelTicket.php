<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_olalevels_tickets')]
#[ORM\UniqueConstraint(name: 'tickets_id_olalevels_id', columns: ['tickets_id', 'olalevels_id'])]
#[ORM\Index(name: 'tickets_id', columns: ['tickets_id'])]
#[ORM\Index(name: 'olalevels_id', columns: ['olalevels_id'])]
class OlalevelTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $tickets_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $olalevels_id;

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

    public function setTicketsId(?int $tickets_id): self
    {
        $this->tickets_id = $tickets_id;

        return $this;
    }

    public function getOlalevelsId(): ?int
    {
        return $this->olalevels_id;
    }

    public function setOlalevelsId(?int $olalevels_id): self
    {
        $this->olalevels_id = $olalevels_id;

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