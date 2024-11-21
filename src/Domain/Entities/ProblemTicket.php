<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_problems_tickets')]
#[ORM\UniqueConstraint(columns: ["problems_id", "tickets_id"])]
#[ORM\Index(name: "tickets_id", columns: ["tickets_id"])]
class ProblemTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $problems_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $tickets_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProblemsId(): ?int
    {
        return $this->problems_id;
    }


    public function setProblemsId(?int $problems_id): self
    {
        $this->problems_id = $problems_id;

        return $this;
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

}
