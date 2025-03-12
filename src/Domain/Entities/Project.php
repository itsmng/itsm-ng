<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_projects')]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "code", columns: ["code"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "projects_id", columns: ["projects_id"])]
#[ORM\Index(name: "projectstates_id", columns: ["projectstates_id"])]
#[ORM\Index(name: "projecttypes_id", columns: ["projecttypes_id"])]
#[ORM\Index(name: "priority", columns: ["priority"])]
#[ORM\Index(name: "date", columns: ["date"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "groups_id", columns: ["groups_id"])]
#[ORM\Index(name: "plan_start_date", columns: ["plan_start_date"])]
#[ORM\Index(name: "plan_end_date", columns: ["plan_end_date"])]
#[ORM\Index(name: "real_start_date", columns: ["real_start_date"])]
#[ORM\Index(name: "real_end_date", columns: ["real_end_date"])]
#[ORM\Index(name: "percent_done", columns: ["percent_done"])]
#[ORM\Index(name: "show_on_global_gantt", columns: ["show_on_global_gantt"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "projecttemplates_id", columns: ["projecttemplates_id"])]
#[ORM\Index(name: "is_template", columns: ["is_template"])]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'code', type: 'string', length: 255, nullable: true)]
    private $code;

    #[ORM\Column(name: 'priority', type: 'integer', options: ['default' => 1])]
    private $priority;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'projects_id', referencedColumnName: 'id', nullable: true)]
    private ?Project $project = null;

    #[ORM\ManyToOne(targetEntity: ProjectState::class)]
    #[ORM\JoinColumn(name: 'projectstates_id', referencedColumnName: 'id', nullable: true)]
    private ?ProjectState $projectstate = null;

    #[ORM\ManyToOne(targetEntity: ProjectType::class)]
    #[ORM\JoinColumn(name: 'projecttypes_id', referencedColumnName: 'id', nullable: true)]
    private ?ProjectType $projecttype = null;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
    private $date;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: false)]
    private $dateMod;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group = null;

    #[ORM\Column(name: 'plan_start_date', type: 'datetime', nullable: true)]
    private $planStartDate;

    #[ORM\Column(name: 'plan_end_date', type: 'datetime', nullable: true)]
    private $planEndDate;

    #[ORM\Column(name: 'real_end_date', type: 'datetime', nullable: true)]
    private $realEndDate;

    #[ORM\Column(name: 'real_start_date', type: 'datetime', nullable: true)]
    private $realStartDate;

    #[ORM\Column(name: 'percent_done', type: 'integer', options: ['default' => 0])]
    private $percentDone;

    #[ORM\Column(name: 'auto_percent_done', type: 'boolean', options: ['default' => 0])]
    private $autoPercentDone;

    #[ORM\Column(name: 'show_on_global_gantt', type: 'boolean', options: ['default' => 0])]
    private $showOnGlobalGantt;

    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(name: 'comment', type: 'text', nullable: true)]
    private $comment;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted;

    #[ORM\Column(name: 'date_creation', type: 'datetime', options: ['default' => null])]
    private $dateCreation;

    #[ORM\Column(name: 'projecttemplates_id', type: 'integer', options: ['default' => 0])]
    private $projecttemplatesId;

    #[ORM\Column(name: 'is_template', type: 'boolean', options: ['default' => 0])]
    private $isTemplate;

    #[ORM\Column(name: 'template_name', type: 'string', length: 255, nullable: true)]
    private $templateName;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getPercentDone(): ?int
    {
        return $this->percentDone;
    }

    public function setPercentDone(?int $percentDone): self
    {
        $this->percentDone = $percentDone;

        return $this;
    }

    public function getAutoPercentDone(): ?bool
    {
        return $this->autoPercentDone;
    }

    public function setAutoPercentDone(?bool $autoPercentDone): self
    {
        $this->autoPercentDone = $autoPercentDone;

        return $this;
    }

    public function getShowOnGlobalGantt(): ?bool
    {
        return $this->showOnGlobalGantt;
    }

    public function setShowOnGlobalGantt(?bool $showOnGlobalGantt): self
    {
        $this->showOnGlobalGantt = $showOnGlobalGantt;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

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

    public function getProjectTemplatesId(): ?int
    {
        return $this->projecttemplatesId;
    }

    public function setProjectTemplatesId(?int $projecttemplatesId): self
    {
        $this->projecttemplatesId = $projecttemplatesId;

        return $this;
    }

    public function getIsTemplate(): ?bool
    {
        return $this->isTemplate;
    }

    public function setIsTemplate(?bool $isTemplate): self
    {
        $this->isTemplate = $isTemplate;

        return $this;
    }

    public function getTemplateName(): ?string
    {
        return $this->templateName;
    }

    public function setTemplateName(?string $templateName): self
    {
        $this->templateName = $templateName;

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
     * Get the value of projecttype
     */
    public function getProjectType()
    {
        return $this->projecttype;
    }

    /**
     * Set the value of projecttype
     *
     * @return  self
     */
    public function setProjectType($projecttype)
    {
        $this->projecttype = $projecttype;

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
     * Get the value of group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set the value of group
     *
     * @return  self
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }
}
