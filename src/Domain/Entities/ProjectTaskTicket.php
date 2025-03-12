<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_projecttasks_tickets')]
#[ORM\UniqueConstraint(name: "unicity", columns: ['tickets_id', 'projecttasks_id'])]
#[ORM\Index(name: "projects_id", columns: ["projecttasks_id"])]
class ProjectTaskTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'projecttaskTickets')]
    #[ORM\JoinColumn(name: 'tickets_id', referencedColumnName: 'id', nullable: true)]
    private ?Ticket $ticket = null;

    #[ORM\ManyToOne(targetEntity: ProjectTask::class, inversedBy: 'projecttaskTickets')]
    #[ORM\JoinColumn(name: 'projecttasks_id', referencedColumnName: 'id', nullable: true)]
    private ?ProjectTask $projecttask = null;

    public function getId(): ?int
    {
        return $this->id;
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
     * Get the value of projecttask
     */
    public function getProjectTask()
    {
        return $this->projecttask;
    }

    /**
     * Set the value of projecttask
     *
     * @return  self
     */
    public function setProjectTask($projecttask)
    {
        $this->projecttask = $projecttask;

        return $this;
    }
}
