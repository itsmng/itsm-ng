<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_reminders')]
#[ORM\UniqueConstraint(name: 'uuid', columns: ['uuid'])]
#[ORM\Index(name: "date", columns: ["date"])]
#[ORM\Index(name: "begin", columns: ["begin"])]
#[ORM\Index(name: "end", columns: ["end"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "is_planned", columns: ["is_planned"])]
#[ORM\Index(name: "state", columns: ["state"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class Reminder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $uuid;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $text;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $begin;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $end;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_planned;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $state;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $begin_view_date;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $end_view_date;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\OneToMany(mappedBy: 'reminder', targetEntity: EntityReminder::class)]
    private Collection $entityReminders;

    #[ORM\OneToMany(mappedBy: 'reminder', targetEntity: GroupReminder::class)]
    private Collection $groupReminders;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getBegin(): ?string
    {
        return $this->begin;
    }

    public function setBegin(?string $begin): self
    {
        $this->begin = $begin;

        return $this;
    }

    public function getEnd(): ?string
    {
        return $this->end;
    }

    public function setEnd(?string $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getIsPlanned(): ?string
    {
        return $this->is_planned;
    }

    public function setIsPlanned(?string $is_planned): self
    {
        $this->is_planned = $is_planned;

        return $this;
    }

    public function getDateMod(): ?string
    {
        return $this->date_mod;
    }

    public function setDateMod(?string $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getBeginViewDate(): ?string
    {
        return $this->begin_view_date;
    }

    public function setBeginViewDate(?string $begin_view_date): self
    {
        $this->begin_view_date = $begin_view_date;

        return $this;
    }

    public function getEndViewDate(): ?string
    {
        return $this->end_view_date;
    }

    public function setEndViewDate(?string $end_view_date): self
    {
        $this->end_view_date = $end_view_date;

        return $this;
    }

    public function getDateCreation(): ?string
    {
        return $this->date_creation;
    }

    public function setDateCreation(?string $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }


    /**
     * Get the value of entityReminders
     */ 
    public function getEntityReminders()
    {
        return $this->entityReminders;
    }

    /**
     * Set the value of entityReminders
     *
     * @return  self
     */ 
    public function setEntityReminders($entityReminders)
    {
        $this->entityReminders = $entityReminders;

        return $this;
    }

    /**
     * Get the value of groupReminders
     */ 
    public function getGroupReminders()
    {
        return $this->groupReminders;
    }

    /**
     * Set the value of groupReminders
     *
     * @return  self
     */ 
    public function setGroupReminders($groupReminders)
    {
        $this->groupReminders = $groupReminders;

        return $this;
    }
}
