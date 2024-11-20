<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_projecttasktemplates')]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "projects_id", columns: ["projects_id"])]
#[ORM\Index(name: "projecttasks_id", columns: ["projecttasks_id"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "plan_start_date", columns: ["plan_start_date"])]
#[ORM\Index(name: "plan_end_date", columns: ["plan_end_date"])]
#[ORM\Index(name: "real_start_date", columns: ["real_start_date"])]
#[ORM\Index(name: "real_end_date", columns: ["real_end_date"])]
#[ORM\Index(name: "percent_done", columns: ["percent_done"])]
#[ORM\Index(name: "projectstates_id", columns: ["projectstates_id"])]
#[ORM\Index(name: "projecttasktypes_id", columns: ["projecttasktypes_id"])]
#[ORM\Index(name: "is_milestone", columns: ["is_milestone"])]
class Projecttasktemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    #[ORM\Column(type: 'text', nullable: true)]
    private $comment;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $projects_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $projecttasks_id;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $plan_start_date;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $plan_end_date;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $real_start_date;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $real_end_date;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $planned_duration;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $effective_duration;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $projectstates_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $projecttasktypes_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $percent_done;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_milestone;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comments;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(?int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getProjectsId(): ?int
    {
        return $this->projects_id;
    }

    public function setProjectsId(?int $projects_id): self
    {
        $this->projects_id = $projects_id;

        return $this;
    }

    public function getProjecttasksId(): ?int
    {
        return $this->projecttasks_id;
    }

    public function setProjecttasksId(?int $projecttasks_id): self
    {
        $this->projecttasks_id = $projecttasks_id;

        return $this;
    }

    public function getPlanStartDate(): ?\DateTimeInterface
    {
        return $this->plan_start_date;
    }

    public function setPlanStartDate(?\DateTimeInterface $plan_start_date): self
    {
        $this->plan_start_date = $plan_start_date;

        return $this;
    }

    public function getPlanEndDate(): ?\DateTimeInterface
    {
        return $this->plan_end_date;
    }

    public function setPlanEndDate(?\DateTimeInterface $plan_end_date): self
    {
        $this->plan_end_date = $plan_end_date;

        return $this;
    }

    public function getRealStartDate(): ?\DateTimeInterface
    {
        return $this->real_start_date;
    }

    public function setRealStartDate(?\DateTimeInterface $real_start_date): self
    {
        $this->real_start_date = $real_start_date;

        return $this;
    }

    public function getRealEndDate(): ?\DateTimeInterface
    {
        return $this->real_end_date;
    }

    public function setRealEndDate(?\DateTimeInterface $real_end_date): self
    {
        $this->real_end_date = $real_end_date;

        return $this;
    }

    public function getPlannedDuration(): ?int
    {
        return $this->planned_duration;
    }

    public function setPlannedDuration(?int $planned_duration): self
    {
        $this->planned_duration = $planned_duration;

        return $this;
    }

    public function getEffectiveDuration(): ?int
    {
        return $this->effective_duration;
    }

    public function setEffectiveDuration(?int $effective_duration): self
    {
        $this->effective_duration = $effective_duration;

        return $this;
    }

    public function getProjectstatesId(): ?int
    {
        return $this->projectstates_id;
    }

    public function setProjectstatesId(?int $projectstates_id): self
    {
        $this->projectstates_id = $projectstates_id;

        return $this;
    }

    public function getProjecttasktypesId(): ?int
    {
        return $this->projecttasktypes_id;
    }

    public function setProjecttasktypesId(?int $projecttasktypes_id): self
    {
        $this->projecttasktypes_id = $projecttasktypes_id;

        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }

    public function setUsersId(?int $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }

    public function getPercentDone(): ?int
    {
        return $this->percent_done;
    }

    public function setPercentDone(?int $percent_done): self
    {
        $this->percent_done = $percent_done;

        return $this;
    }

    public function getIsMilestone(): ?bool
    {
        return $this->is_milestone;
    }

    public function setIsMilestone(?bool $is_milestone): self
    {
        $this->is_milestone = $is_milestone;

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

}
