<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'uuid', type: 'string', length: 255, nullable: true)]
    private $uuid;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
    private $date;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'text', type: 'text', length: 65535, nullable: true)]
    private $text;

    #[ORM\Column(name: '`begin`', type: 'datetime', nullable: true)]
    private $begin;

    #[ORM\Column(name: '`end`', type: 'datetime', nullable: true)]
    private $end;

    #[ORM\Column(name: 'is_planned', type: 'boolean', options: ['default' => 0])]
    private $isPlanned = 0;

    #[ORM\Column(name: 'date_mod', type: 'datetime')]
    private $dateMod;

    #[ORM\Column(name: 'state', type: 'integer', options: ['default' => 0])]
    private $state = 0;

    #[ORM\Column(name: 'begin_view_date', type: 'datetime', nullable: true)]
    private $beginViewDate;

    #[ORM\Column(name: 'end_view_date', type: 'datetime', nullable: true)]
    private $endViewDate;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    #[ORM\OneToMany(mappedBy: 'reminder', targetEntity: EntityReminder::class)]
    private Collection $entityReminders;

    #[ORM\OneToMany(mappedBy: 'reminder', targetEntity: GroupReminder::class)]
    private Collection $groupReminders;

    #[ORM\OneToMany(mappedBy: 'reminder', targetEntity: ProfileReminder::class)]
    private Collection $profileReminders;

    #[ORM\OneToMany(mappedBy: 'reminder', targetEntity: ReminderUser::class)]
    private Collection $reminderUsers;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }


    public function setDate(DateTimeInterface|string|null $date): self
    {
        if ($date === null || $date === '' || $date === '0000-00-00 00:00:00') {
            $this->date = null;
        } elseif (is_string($date)) {
            try {
                $this->date = new \DateTime($date);
            } catch (\Exception $e) {
                $this->date = null;
            }
        } else {
            $this->date = $date;
        }

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

    public function getBegin(): ?\DateTimeInterface
    {
        return $this->begin;
    }

    public function setBegin(\DateTimeInterface|string|null $begin): self
    {
        if ($begin === null || $begin === '' || $begin === '0000-00-00 00:00:00') {
            $this->begin = null;
        } elseif (is_string($begin)) {
            try {
                $this->begin = new \DateTime($begin);
            } catch (\Exception $e) {
                $this->begin = null;
            }
        } else {
            $this->begin = $begin;
        }

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface|string|null $end): self
    {
        if ($end === null || $end === '' || $end === '0000-00-00 00:00:00') {
            $this->end = null;
        } elseif (is_string($end)) {
            try {
                $this->end = new \DateTime($end);
            } catch (\Exception $e) {
                $this->end = null;
            }
        } else {
            $this->end = $end;
        }

        return $this;
    }

    public function getIsPlanned(): ?bool
    {
        return $this->isPlanned;
    }

    public function setIsPlanned(bool|int|string|null $isPlanned): self
    {
        if ($isPlanned === null || $isPlanned === '') {
            $this->isPlanned = 0;
        } else {
            $this->isPlanned = (bool)$isPlanned;
        }
        return $this;
    }

    public function getDateMod(): DateTime
    {
        return $this->dateMod ?? new DateTime();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setDateMod(): self
    {
        $this->dateMod = new DateTime();

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int|string|null $state): self
    {
        if ($state === null || $state === '') {
            $this->state = 0;
        } else {
            $this->state = (int)$state;
        }

        return $this;
    }

    public function getBeginViewDate(): ?\DateTimeInterface
    {
        return $this->beginViewDate;
    }

    public function setBeginViewDate(\DateTimeInterface|string|null $beginViewDate): self
    {
        if ($beginViewDate === null || $beginViewDate === '' || $beginViewDate === '0000-00-00 00:00:00') {
            $this->beginViewDate = null;
        } elseif (is_string($beginViewDate)) {
            try {
                $this->beginViewDate = new \DateTime($beginViewDate);
            } catch (\Exception $e) {
                $this->beginViewDate = null;
            }
        } else {
            $this->beginViewDate = $beginViewDate;
        }

        return $this;
    }

    public function getEndViewDate(): ?\DateTimeInterface
    {
        return $this->endViewDate;
    }

    public function setEndViewDate(\DateTimeInterface|string|null $endViewDate): self
    {
        if ($endViewDate === null || $endViewDate === '' || $endViewDate === '0000-00-00 00:00:00') {
            $this->endViewDate = null;
        } elseif (is_string($endViewDate)) {
            try {
                $this->endViewDate = new \DateTime($endViewDate);
            } catch (\Exception $e) {
                $this->endViewDate = null;
            }
        } else {
            $this->endViewDate = $endViewDate;
        }

        return $this;
    }

    public function getDateCreation(): DateTime
    {
        return $this->dateCreation ?? new DateTime();
    }

    #[ORM\PrePersist]
    public function setDateCreation(): self
    {
        $this->dateCreation = new DateTime();

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

    /**
     * Get the value of profileReminders
     */
    public function getProfileReminders()
    {
        return $this->profileReminders;
    }

    /**
     * Set the value of profileReminders
     *
     * @return  self
     */
    public function setProfileReminders($profileReminders)
    {
        $this->profileReminders = $profileReminders;

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


    /**
     * Get the value of reminderUsers
     */
    public function getReminderUsers()
    {
        return $this->reminderUsers;
    }

    /**
     * Set the value of reminderUsers
     *
     * @return  self
     */
    public function setReminderUsers($reminderUsers)
    {
        $this->reminderUsers = $reminderUsers;

        return $this;
    }
}
