<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_tickettasks")]
#[ORM\UniqueConstraint(name: "uuid", columns: ["uuid"])]
#[ORM\Index(name: "date", columns: ["date"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "users_id_editor", columns: ["users_id_editor"])]
#[ORM\Index(name: "tickets_id", columns: ["tickets_id"])]
#[ORM\Index(name: "is_private", columns: ["is_private"])]
#[ORM\Index(name: "taskcategories_id", columns: ["taskcategories_id"])]
#[ORM\Index(name: "state", columns: ["state"])]
#[ORM\Index(name: "users_id_tech", columns: ["users_id_tech"])]
#[ORM\Index(name: "groups_id_tech", columns: ["groups_id_tech"])]
#[ORM\Index(name: "begin", columns: ["begin"])]
#[ORM\Index(name: "end", columns: ["end"])]
#[ORM\Index(name: "tasktemplates_id", columns: ["tasktemplates_id"])]
#[ORM\Index(name: "sourceitems_id", columns: ["sourceitems_id"])]
class TicketTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $uuid;

    #[ORM\ManyToOne(targetEntity: Ticket::class)]
    #[ORM\JoinColumn(name: 'tickets_id', referencedColumnName: 'id', nullable: true)]
    private ?Ticket $ticket;

    #[ORM\ManyToOne(targetEntity: Taskcategory::class)]
    #[ORM\JoinColumn(name: 'taskcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?Taskcategory $taskcategory;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_editor', referencedColumnName: 'id', nullable: true)]
    private ?User $userEditor;

    #[ORM\Column(type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_private;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $actiontime;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $begin;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $end;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $state;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_tech', referencedColumnName: 'id', nullable: true)]
    private ?User $userTech;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id_tech', referencedColumnName: 'id', nullable: true)]
    private ?Group $groupTech;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\ManyToOne(targetEntity: Tasktemplate::class)]
    #[ORM\JoinColumn(name: 'tasktemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?Tasktemplate $tasktemplate;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $timeline_position;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $sourceitems_id;

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

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getIsPrivate(): ?bool
    {
        return $this->is_private;
    }

    public function setIsPrivate(?bool $is_private): self
    {
        $this->is_private = $is_private;

        return $this;
    }

    public function getActiontime(): ?int
    {
        return $this->actiontime;
    }

    public function setActiontime(?int $actiontime): self
    {
        $this->actiontime = $actiontime;

        return $this;
    }

    public function getBegin(): ?\DateTime
    {
        return $this->begin;
    }

    public function setBegin(?\DateTime $begin): self
    {
        $this->begin = $begin;

        return $this;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    public function setEnd(?\DateTime $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(?int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getDateMod(): ?\DateTime
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTime $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTime $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getTimelinePosition(): ?bool
    {
        return $this->timeline_position;
    }

    public function setTimelinePosition(?bool $timeline_position): self
    {
        $this->timeline_position = $timeline_position;

        return $this;
    }

    public function getSourceitemsId(): ?int
    {
        return $this->sourceitems_id;
    }

    public function setSourceitemsId(?int $sourceitems_id): self
    {
        $this->sourceitems_id = $sourceitems_id;

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
     * Get the value of taskcategory
     */
    public function getTaskcategory()
    {
        return $this->taskcategory;
    }

    /**
     * Set the value of taskcategory
     *
     * @return  self
     */
    public function setTaskcategory($taskcategory)
    {
        $this->taskcategory = $taskcategory;

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
     * Get the value of userEditor
     */
    public function getUserEditor()
    {
        return $this->userEditor;
    }

    /**
     * Set the value of userEditor
     *
     * @return  self
     */
    public function setUserEditor($userEditor)
    {
        $this->userEditor = $userEditor;

        return $this;
    }

    /**
     * Get the value of userTech
     */
    public function getUserTech()
    {
        return $this->userTech;
    }

    /**
     * Set the value of userTech
     *
     * @return  self
     */
    public function setUserTech($userTech)
    {
        $this->userTech = $userTech;

        return $this;
    }

    /**
     * Get the value of groupTech
     */
    public function getGroupTech()
    {
        return $this->groupTech;
    }

    /**
     * Set the value of groupTech
     *
     * @return  self
     */
    public function setGroupTech($groupTech)
    {
        $this->groupTech = $groupTech;

        return $this;
    }

    /**
     * Get the value of tasktemplate
     */
    public function getTasktemplate()
    {
        return $this->tasktemplate;
    }

    /**
     * Set the value of tasktemplate
     *
     * @return  self
     */
    public function setTasktemplate($tasktemplate)
    {
        $this->tasktemplate = $tasktemplate;

        return $this;
    }
}
