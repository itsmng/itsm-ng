<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_reminders_users')]
#[ORM\Index(name: "reminders_id", columns: ["reminders_id"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
class ReminderUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $reminders_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRemindersId(): ?string
    {
        return $this->reminders_id;
    }

    public function setRemindersId(?string $reminders_id): self
    {
        $this->reminders_id = $reminders_id;

        return $this;
    }

    public function getUsersId(): ?string
    {
        return $this->users_id;
    }

    public function setUsersId(?string $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }

}
