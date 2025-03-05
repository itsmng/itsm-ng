<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_problemtasks')]
#[ORM\UniqueConstraint(name: "uuid", columns: ["uuid"])]
#[ORM\Index(name: "problems_id", columns: ["problems_id"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "users_id_editor", columns: ["users_id_editor"])]
#[ORM\Index(name: "tech_users_id", columns: ["tech_users_id"])]
#[ORM\Index(name: "tech_groups_id", columns: ["tech_groups_id"])]
#[ORM\Index(name: "date", columns: ["date"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "begin", columns: ["begin"])]
#[ORM\Index(name: "end", columns: ["end"])]
#[ORM\Index(name: "state", columns: ["state"])]
#[ORM\Index(name: "taskcategories_id", columns: ["taskcategories_id"])]
#[ORM\Index(name: "tasktemplates_id", columns: ["tasktemplates_id"])]
#[ORM\Index(name: "is_private", columns: ["is_private"])]

class Problemtask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'uuid', type: 'string', length: 255, nullable: true)]
    private $uuid;

    #[ORM\ManyToOne(targetEntity: Problem::class)]
    #[ORM\JoinColumn(name: 'problems_id', referencedColumnName: 'id', nullable: true)]
    private ?Problem $problem = null;

    #[ORM\ManyToOne(targetEntity: Taskcategory::class)]
    #[ORM\JoinColumn(name: 'taskcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?Taskcategory $taskcategory = null;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
    private $date;

    #[ORM\Column(name: 'begin', type: 'datetime', nullable: true)]
    private $begin;

    #[ORM\Column(name: 'end', type: 'datetime', nullable: true)]
    private $end;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_editor', referencedColumnName: 'id', nullable: true)]
    private ?User $userEditor = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'tech_groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $techGroup = null;

    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(name: 'actiontime', type: 'integer', options: ['default' => 0])]
    private $actiontime;

    #[ORM\Column(name: 'state', type: 'integer', options: ['default' => 0])]
    private $state;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    #[ORM\ManyToOne(targetEntity: Tasktemplate::class)]
    #[ORM\JoinColumn(name: 'tasktemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?Tasktemplate $tasktemplate = null;

    #[ORM\Column(name: 'timeline_position', type: 'boolean', options: ['default' => 0])]
    private $timelinePosition;

    #[ORM\Column(name: 'is_private', type: 'boolean', options: ['default' => 0])]
    private $isPrivate;

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


    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getBegin(): ?\DateTimeInterface
    {
        return $this->begin;
    }


    public function setBegin(?\DateTimeInterface $begin): self
    {
        $this->begin = $begin;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }


    public function setEnd(?\DateTimeInterface $end): self
    {
        $this->end = $end;

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

    public function getActiontime(): ?int
    {
        return $this->actiontime;
    }


    public function setActiontime(?int $actiontime): self
    {
        $this->actiontime = $actiontime;

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

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }


    public function setDateMod(?\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }


    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getTimelinePosition(): ?int
    {
        return $this->timelinePosition;
    }


    public function setTimelinePosition(?int $timelinePosition): self
    {
        $this->timelinePosition = $timelinePosition;

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


    /**
     * Get the value of problem
     */
    public function getProblem()
    {
        return $this->problem;
    }

    /**
     * Set the value of problem
     *
     * @return  self
     */
    public function setProblem($problem)
    {
        $this->problem = $problem;

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
