<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_groups_tickets")]
#[ORM\UniqueConstraint(columns: ["tickets_id", "type", "groups_id"])]
#[ORM\Index(columns: ["groups_id", "type"])]
class GroupTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ['default' => 0])]
    private $tickets_id;

    #[ORM\Column(type: "integer", options: ['default' => 0])]
    private $groups_id;

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
}
