<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_changes_tickets')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['changes_id', 'tickets_id'])]
#[ORM\Index(name: 'tickets_id', columns: ['tickets_id'])]
class ChangeTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $changes_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $tickets_id;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getChangesId(): int
    {
        return $this->changes_id;
    }

    public function setChangesId(int $changes_id): self
    {
        $this->changes_id = $changes_id;

        return $this;
    }

    public function getTicketsId(): int
    {
        return $this->tickets_id;
    }

    public function setTicketsId(int $problems_id): self
    {
        $this->tickets_id = $problems_id;

        return $this;
    }
}
