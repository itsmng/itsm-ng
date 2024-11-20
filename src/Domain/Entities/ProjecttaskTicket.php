<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_projecttasks_tickets')]
#[ORM\UniqueConstraint(columns: ['tickets_id', 'projecttasks_id'])]
#[ORM\Index(name: "projecttasks_id", columns: ["projecttasks_id"])]
class ProjecttaskTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $tickets_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $projecttasks_id;

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

    public function getProjecttasksId(): ?int
    {
        return $this->projecttasks_id;
    }

    public function setProjecttasksId(?int $projecttasks_id): self
    {
        $this->projecttasks_id = $projecttasks_id;

        return $this;
    }

}