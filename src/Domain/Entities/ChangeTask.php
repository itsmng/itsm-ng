<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use TaskTemplate;

#[ORM\Entity]
#[ORM\Table(name: "glpi_changetasks")]
#[ORM\UniqueConstraint(name: "uuid", columns: ["uuid"])]
#[ORM\Index(name: 'changes_id', columns: ["changes_id"])]
#[ORM\Index(name: 'state', columns: ["state"])]
#[ORM\Index(name: 'users_id', columns: ["users_id"])]
#[ORM\Index(name: 'users_id_editor', columns: ["users_id_editor"])]
#[ORM\Index(name: 'users_id_tech', columns: ["users_id_tech"])]
#[ORM\Index(name: 'groups_id_tech', columns: ["groups_id_tech"])]
#[ORM\Index(name: 'date', columns: ["date"])]
#[ORM\Index(name: 'date_mod', columns: ["date_mod"])]
#[ORM\Index(name: 'date_creation', columns: ["date_creation"])]
#[ORM\Index(name: 'begin', columns: ["begin"])]
#[ORM\Index(name: 'end', columns: ["end"])]
#[ORM\Index(name: 'taskcategories_id', columns: ["taskcategories_id"])]
#[ORM\Index(name: 'tasktemplates_id', columns: ["tasktemplates_id"])]
#[ORM\Index(name: 'is_private', columns: ["is_private"])]
class ChangeTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $uuid;

    #[ORM\ManyToOne(targetEntity: Change::class)]
    #[ORM\JoinColumn(name: 'changes_id', referencedColumnName: 'id', nullable: true)]
    private ?Change $change;

    #[ORM\ManyToOne(targetEntity: Taskcategory::class)]
    #[ORM\JoinColumn(name: 'taskcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?Taskcategory $taskcategory;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $state;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $begin;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $end;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_editor', referencedColumnName: 'id', nullable: true)]
    private ?User $user_editor;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'tech_groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $techGroup;

    #[ORM\Column(type: "text", nullable: true)]
    private $content;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $actiontime;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

    #[ORM\ManyToOne(targetEntity: TaskTemplate::class)]
    #[ORM\JoinColumn(name: 'tasktemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?TaskTemplate $tasktemplate;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $timeline_position;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_private;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getBegin(): ?\DateTimeInterface
    {
        return $this->begin;
    }

    public function setBegin(\DateTimeInterface $begin): self
    {
        $this->begin = $begin;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): self
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

    public function setActiontime(int $actiontime): self
    {
        $this->actiontime = $actiontime;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }


    public function getTimelinePosition(): ?int
    {
        return $this->timeline_position;
    }

    public function setTimelinePosition(int $timeline_position): self
    {
        $this->timeline_position = $timeline_position;

        return $this;
    }

    public function getIsPrivate(): ?int
    {
        return $this->is_private;
    }

    public function setIsPrivate(int $is_private): self
    {
        $this->is_private = $is_private;

        return $this;
    }

    /**
     * Get the value of change
     */
    public function getChange()
    {
        return $this->change;
    }

    /**
     * Set the value of change
     *
     * @return  self
     */
    public function setChange($change)
    {
        $this->change = $change;

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
     * Get the value of user_editor
     */
    public function getUser_editor()
    {
        return $this->user_editor;
    }

    /**
     * Set the value of user_editor
     *
     * @return  self
     */
    public function setUser_editor($user_editor)
    {
        $this->user_editor = $user_editor;

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
}
