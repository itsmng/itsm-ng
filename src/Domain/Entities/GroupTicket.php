<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_groups_tickets")]
#[ORM\UniqueConstraint(name: 'unicity', columns: ["tickets_id", "type", "groups_id"])]
#[ORM\Index(name: 'group', columns: ["groups_id", "type"])]
class GroupTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", name: 'tickets_id', options: ['default' => 0])]
    private $tickets_id;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'groupTickets')]
    #[ORM\JoinColumn(name: 'tickets_id', referencedColumnName: 'id', nullable: false)]
    private ?Ticket $ticket;

    #[ORM\Column(type: "integer", name: 'groups_id', options: ['default' => 0])]
    private $groups_id;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'groupTickets')]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: false)]
    private ?Group $group;

    #[ORM\Column(type: "integer", options: ['default' => 1])]
    private $type;

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

    public function getGroupsId(): ?int
    {
        return $this->groups_id;
    }

    public function setGroupsId(int $groups_id): self
    {
        $this->groups_id = $groups_id;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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
     * Get the value of group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set the value of group
     *
     * @return  self
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }
}
