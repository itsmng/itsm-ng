<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_tickets_users")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["tickets_id", "type", "users_id", "alternative_email"])]
#[ORM\Index(name: "user", columns: ["users_id", "type"])]
class TicketUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'tickets_id', type: 'integer', options: ['default' => 0])]
    private $ticketsId = 0;

    #[ORM\Column(name: 'users_id', type: 'integer', options: ['default' => 0])]
    private $usersId = 0;

    #[ORM\Column(name: 'type', type: 'integer', options: ['default' => 1])]
    private $type = 1;

    #[ORM\Column(name: 'use_notification', type: 'boolean', options: ['default' => 1])]
    private $useNotification = 1;

    #[ORM\Column(name: 'alternative_email', type: 'string', length: 255, nullable: true)]
    private $alternativeEmail;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicketsId(): ?int
    {
        return $this->ticketsId;
    }

    public function setTicketsId(?int $ticketsId): self
    {
        $this->ticketsId = $ticketsId;

        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->usersId;
    }

    public function setUsersId(?int $usersId): self
    {
        $this->usersId = $usersId;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUseNotification(): ?bool
    {
        return $this->useNotification;
    }

    public function setUseNotification(?bool $useNotification): self
    {
        $this->useNotification = $useNotification;

        return $this;
    }

    public function getAlternativeEmail(): ?string
    {
        return $this->alternativeEmail;
    }

    public function setAlternativeEmail(?string $alternativeEmail): self
    {
        $this->alternativeEmail = $alternativeEmail;

        return $this;
    }

}
