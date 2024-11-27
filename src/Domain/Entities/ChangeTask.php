<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use TaskTemplate;

#[ORM\Entity]
#[ORM\Table(name: "glpi_changetasks")]
#[ORM\UniqueConstraint(name: "uuid", columns: ["uuid"])]
#[ORM\Index(columns: ["changes_id"])]
#[ORM\Index(columns: ["state"])]
#[ORM\Index(columns: ["users_id"])]
#[ORM\Index(columns: ["users_id_editor"])]
#[ORM\Index(columns: ["users_id_tech"])]
#[ORM\Index(columns: ["groups_id_tech"])]
#[ORM\Index(columns: ["date"])]
#[ORM\Index(columns: ["date_mod"])]
#[ORM\Index(columns: ["date_creation"])]
#[ORM\Index(columns: ["begin"])]
#[ORM\Index(columns: ["end"])]
#[ORM\Index(columns: ["taskcategories_id"])]
#[ORM\Index(columns: ["tasktemplates_id"])]
#[ORM\Index(columns: ["is_private"])]
class ChangeTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $uuid;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $changes_id;

    #[ORM\ManyToOne(targetEntity: Change::class)]
    #[ORM\JoinColumn(name: 'changes_id', referencedColumnName: 'id', nullable: false)]
    private ?Change $change;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $taskcategories_id;

    #[ORM\ManyToOne(targetEntity: Taskcategory::class)]
    #[ORM\JoinColumn(name: 'taskcategories_id', referencedColumnName: 'id', nullable: false)]
    private ?Taskcategory $taskcategory;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $state;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $begin;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $end;

    #[ORM\Column(type: "integer", name: "users_id", options: ["default" => 0])]
    private $users_id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user;

    #[ORM\Column(type: "integer", name: "users_id_editor", options: ["default" => 0])]
    private $users_id_editor;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_editor', referencedColumnName: 'id', nullable: false)]
    private ?User $user_editor;

    #[ORM\Column(type: "integer", name: "users_id_tech", options: ["default" => 0])]
    private $users_id_tech;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_tech', referencedColumnName: 'id', nullable: false)]
    private ?User $user_tech;

    #[ORM\Column(type: "integer", name: "groups_id_tech", options: ["default" => 0])]
    private $groups_id_tech;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id_tech', referencedColumnName: 'id', nullable: false)]
    private ?Group $group_tech;

    #[ORM\Column(type: "text", nullable: true)]
    private $content;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $actiontime;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $tasktemplates_id;

    #[ORM\ManyToOne(targetEntity: TaskTemplate::class)]
    #[ORM\JoinColumn(name: 'tasktemplates_id', referencedColumnName: 'id', nullable: false)]
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

    public function getChangesId(): ?int
    {
        return $this->changes_id;
    }

    public function setChangesId(int $changes_id): self
    {
        $this->changes_id = $changes_id;

        return $this;
    }

    public function getTaskCategoriesId(): ?int
    {
        return $this->taskcategories_id;
    }

    public function setTaskCategoriesId(int $taskcategories_id): self
    {
        $this->taskcategories_id = $taskcategories_id;

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

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }

    public function setUsersId(int $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }

    public function getUsersIdEditor(): ?int
    {
        return $this->users_id_editor;
    }

    public function setUsersIdEditor(int $users_id_editor): self
    {
        $this->users_id_editor = $users_id_editor;

        return $this;
    }

    public function getUsersIdTech(): ?int
    {
        return $this->users_id_tech;
    }

    public function setUsersIdTech(int $users_id_tech): self
    {
        $this->users_id_tech = $users_id_tech;

        return $this;
    }

    public function getGroupsIdTech(): ?int
    {
        return $this->groups_id_tech;
    }

    public function setGroupsIdTech(int $groups_id_tech): self
    {
        $this->groups_id_tech = $groups_id_tech;

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

    public function getTaskTemplatesId(): ?int
    {
        return $this->tasktemplates_id;
    }

    public function setTaskTemplatesId(int $tasktemplates_id): self
    {
        $this->tasktemplates_id = $tasktemplates_id;

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
     * Get the value of user_tech
     */ 
    public function getUser_tech()
    {
        return $this->user_tech;
    }

    /**
     * Set the value of user_tech
     *
     * @return  self
     */ 
    public function setUser_tech($user_tech)
    {
        $this->user_tech = $user_tech;

        return $this;
    }

    /**
     * Get the value of group_tech
     */ 
    public function getGroup_tech()
    {
        return $this->group_tech;
    }

    /**
     * Set the value of group_tech
     *
     * @return  self
     */ 
    public function setGroup_tech($group_tech)
    {
        $this->group_tech = $group_tech;

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
