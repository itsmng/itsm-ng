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

    #[ORM\ManyToOne(targetEntity: Reminder::class, inversedBy: 'reminderUsers')]
    #[ORM\JoinColumn(name: 'reminders_id', referencedColumnName: 'id', nullable: true)]
    private ?Reminder $reminder;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reminderUsers')]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of reminder
     */ 
    public function getReminder()
    {
        return $this->reminder;
    }

    /**
     * Set the value of reminder
     *
     * @return  self
     */ 
    public function setReminder($reminder)
    {
        $this->reminder = $reminder;

        return $this;
    }

    /**
     * Get the value of user
     */ 
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */ 
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
