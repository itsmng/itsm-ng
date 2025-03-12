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
class ProjectTaskTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private $description;

    #[ORM\Column(name: 'comment', type: 'text', nullable: true)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'projects_id', referencedColumnName: 'id', nullable: true)]
    private ?Project $project = null;

    #[ORM\ManyToOne(targetEntity: ProjectTask::class)]
    #[ORM\JoinColumn(name: 'projecttasks_id', referencedColumnName: 'id', nullable: true)]
    private ?ProjectTask $projecttask = null;

    #[ORM\Column(name: 'plan_start_date', type: 'datetime', nullable: true)]
    private $planStartDate;

    #[ORM\Column(name: 'plan_end_date', type: 'datetime', nullable: true)]
    private $planEndDate;

    #[ORM\Column(name: 'real_start_date', type: 'datetime', nullable: true)]
    private $realStartDate;

    #[ORM\Column(name: 'real_end_date', type: 'datetime', nullable: true)]
    private $realEndDate;

    #[ORM\Column(name: 'planned_duration', type: 'integer', options: ['default' => 0])]
    private $plannedDuration;

    #[ORM\Column(name: 'effective_duration', type: 'integer', options: ['default' => 0])]
    private $effectiveDuration;

    #[ORM\ManyToOne(targetEntity: ProjectState::class)]
    #[ORM\JoinColumn(name: 'projectstates_id', referencedColumnName: 'id', nullable: true)]
    private ?ProjectState $projectstate = null;

    #[ORM\ManyToOne(targetEntity: ProjectTaskType::class)]
    #[ORM\JoinColumn(name: 'projecttasktypes_id', referencedColumnName: 'id', nullable: true)]
    private ?ProjectTaskType $projecttasktype = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\Column(name: 'percent_done', type: 'integer', options: ['default' => 0])]
    private $percentDone;

    #[ORM\Column(name: 'is_milestone', type: 'boolean', options: ['default' => 0])]
    private $isMilestone;

    #[ORM\Column(name: 'comments', type: 'text', length: 65535, nullable: true)]
    private $comments;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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

    public function getPlanStartDate(): ?\DateTimeInterface
    {
        return $this->planStartDate;
    }

    public function setPlanStartDate(?\DateTimeInterface $planStartDate): self
    {
        $this->planStartDate = $planStartDate;

        return $this;
    }

    public function getPlanEndDate(): ?\DateTimeInterface
    {
        return $this->planEndDate;
    }

    public function setPlanEndDate(?\DateTimeInterface $planEndDate): self
    {
        $this->planEndDate = $planEndDate;

        return $this;
    }

    public function getRealStartDate(): ?\DateTimeInterface
    {
        return $this->realStartDate;
    }

    public function setRealStartDate(?\DateTimeInterface $realStartDate): self
    {
        $this->realStartDate = $realStartDate;

        return $this;
    }

    public function getRealEndDate(): ?\DateTimeInterface
    {
        return $this->realEndDate;
    }

    public function setRealEndDate(?\DateTimeInterface $realEndDate): self
    {
        $this->realEndDate = $realEndDate;

        return $this;
    }

    public function getPlannedDuration(): ?int
    {
        return $this->plannedDuration;
    }

    public function setPlannedDuration(?int $plannedDuration): self
    {
        $this->plannedDuration = $plannedDuration;

        return $this;
    }

    public function getEffectiveDuration(): ?int
    {
        return $this->effectiveDuration;
    }

    public function setEffectiveDuration(?int $effectiveDuration): self
    {
        $this->effectiveDuration = $effectiveDuration;

        return $this;
    }

    public function getPercentDone(): ?int
    {
        return $this->percentDone;
    }

    public function setPercentDone(?int $percentDone): self
    {
        $this->percentDone = $percentDone;

        return $this;
    }

    public function getIsMilestone(): ?bool
    {
        return $this->isMilestone;
    }

    public function setIsMilestone(?bool $isMilestone): self
    {
        $this->isMilestone = $isMilestone;

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


    /**
     * Get the value of entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the value of entity
     *
     * @return  self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get the value of project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set the value of project
     *
     * @return  self
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get the value of projecttask
     */
    public function getProjectTask()
    {
        return $this->projecttask;
    }

    /**
     * Set the value of projecttask
     *
     * @return  self
     */
    public function setProjectTask($projecttask)
    {
        $this->projecttask = $projecttask;

        return $this;
    }

    /**
     * Get the value of projectstate
     */
    public function getProjectState()
    {
        return $this->projectstate;
    }

    /**
     * Set the value of projectstate
     *
     * @return  self
     */
    public function setProjectState($projectstate)
    {
        $this->projectstate = $projectstate;

        return $this;
    }

    /**
     * Get the value of projecttasktype
     */
    public function getProjectTaskType()
    {
        return $this->projecttasktype;
    }

    /**
     * Set the value of projecttasktype
     *
     * @return  self
     */
    public function setProjectTaskType($projecttasktype)
    {
        $this->projecttasktype = $projecttasktype;

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
