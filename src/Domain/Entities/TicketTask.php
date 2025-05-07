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
#[ORM\Index(name: "editor_users_id", columns: ["editor_users_id"])]
#[ORM\Index(name: "tickets_id", columns: ["tickets_id"])]
#[ORM\Index(name: "is_private", columns: ["is_private"])]
#[ORM\Index(name: "taskcategories_id", columns: ["taskcategories_id"])]
#[ORM\Index(name: "state", columns: ["state"])]
#[ORM\Index(name: "tech_users_id", columns: ["tech_users_id"])]
#[ORM\Index(name: "tech_groups_id", columns: ["tech_groups_id"])]
#[ORM\Index(name: "begin", columns: ["begin"])]
#[ORM\Index(name: "end", columns: ["end"])]
#[ORM\Index(name: "tasktemplates_id", columns: ["tasktemplates_id"])]
#[ORM\Index(name: "sourceitems_id", columns: ["sourceitems_id"])]
class TicketTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'uuid', type: 'string', length: 255, nullable: true)]
    private $uuid = null;

    #[ORM\ManyToOne(targetEntity: Ticket::class)]
    #[ORM\JoinColumn(name: 'tickets_id', referencedColumnName: 'id', nullable: true)]
    private ?Ticket $ticket = null;

    #[ORM\ManyToOne(targetEntity: Taskcategory::class)]
    #[ORM\JoinColumn(name: 'taskcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?Taskcategory $taskcategory = null;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
    private $date = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'editor_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $editorUser = null;

    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    private $content = null;

    #[ORM\Column(name: 'is_private', type: 'boolean', options: ['default' => 0])]
    private $isPrivate = 0;

    #[ORM\Column(name: 'actiontime', type: 'integer', options: ['default' => 0])]
    private $actiontime = 0;

    #[ORM\Column(name: 'begin', type: 'datetime', nullable: true)]
    private $begin = null;

    #[ORM\Column(name: 'end', type: 'datetime', nullable: true)]
    private $end = null;

    #[ORM\Column(name: 'state', type: 'integer', options: ['default' => 1])]
    private $state = 1;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'tech_groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $techGroup = null;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    #[ORM\ManyToOne(targetEntity: TaskTemplate::class)]
    #[ORM\JoinColumn(name: 'tasktemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?TaskTemplate $tasktemplate = null;

    #[ORM\Column(name: 'timeline_position', type: 'boolean', options: ['default' => 0])]
    private $timelinePosition = 0;

    #[ORM\Column(name: 'sourceitems_id', type: 'integer', options: ['default' => 0])]
    private $sourceitemsId = 0;

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
        return $this->isPrivate;
    }

    public function setIsPrivate(?bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;

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
        return $this->dateMod;
    }

    public function setDateMod(?\DateTime $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTime $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getTimelinePosition(): ?bool
    {
        return $this->timelinePosition;
    }

    public function setTimelinePosition(?bool $timelinePosition): self
    {
        $this->timelinePosition = $timelinePosition;

        return $this;
    }

    public function getSourceitemsId(): ?int
    {
        return $this->sourceitemsId;
    }

    public function setSourceitemsId(?int $sourceitemsId): self
    {
        $this->sourceitemsId = $sourceitemsId;

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
    public function getTaskcategory(): ?Taskcategory
    {
        return $this->taskcategory;
    }

    /**
     * Set the value of taskcategory
     *
     * @param Taskcategory|null $taskcategory
     * @return self
     */
    public function setTaskcategory(?Taskcategory $taskcategory): self
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

    /**
     * Get the value of editorUser
     */
    public function getEditorUser()
    {
        return $this->editorUser;
    }

    /**
     * Set the value of editorUser
     *
     * @return  self
     */
    public function setEditorUser($editorUser)
    {
        $this->editorUser = $editorUser;

        return $this;
    }

    /**
     * Get the value of techGroup
     */
    public function getTechGroup()
    {
        return $this->techGroup;
    }

    /**
     * Set the value of techGroup
     *
     * @return  self
     */
    public function setTechGroup($techGroup)
    {
        $this->techGroup = $techGroup;

        return $this;
    }

    /**
     * Get the value of techUser
     */
    public function getTechUser()
    {
        return $this->techUser;
    }

    /**
     * Set the value of techUser
     *
     * @return  self
     */
    public function setTechUser($techUser)
    {
        $this->techUser = $techUser;

        return $this;
    }
}
