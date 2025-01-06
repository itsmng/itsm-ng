<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_projecttasks_tickets')]
#[ORM\UniqueConstraint(name: "unicity", columns: ['tickets_id', 'projecttasks_id'])]
#[ORM\Index(name: "projects_id", columns: ["projecttasks_id"])]
class ProjecttaskTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'projecttaskTickets')]
    #[ORM\JoinColumn(name: 'tickets_id', referencedColumnName: 'id', nullable: true)]
    private ?Ticket $ticket;

    #[ORM\ManyToOne(targetEntity: Projecttask::class, inversedBy: 'projecttaskTickets')]
    #[ORM\JoinColumn(name: 'projecttasks_id', referencedColumnName: 'id', nullable: true)]
    private ?Projecttask $projecttask;

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
    public function getProjecttask()
    {
        return $this->projecttask;
    }

    /**
     * Set the value of projecttask
     *
     * @return  self
     */
    public function setProjecttask($projecttask)
    {
        $this->projecttask = $projecttask;

        return $this;
    }
}
