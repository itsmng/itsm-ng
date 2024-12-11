<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_projecttasks')]
#[ORM\UniqueConstraint(name: 'uuid', columns: ['uuid'])]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "projects_id", columns: ["projects_id"])]
#[ORM\Index(name: "projecttasks_id", columns: ["projecttasks_id"])]
#[ORM\Index(name: "date", columns: ["date"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "plan_start_date", columns: ["plan_start_date"])]
#[ORM\Index(name: "plan_end_date", columns: ["plan_end_date"])]
#[ORM\Index(name: "real_start_date", columns: ["real_start_date"])]
#[ORM\Index(name: "real_end_date", columns: ["real_end_date"])]
#[ORM\Index(name: "percent_done", columns: ["percent_done"])]
#[ORM\Index(name: "projectstates_id", columns: ["projectstates_id"])]
#[ORM\Index(name: "projecttasktypes_id", columns: ["projecttasktypes_id"])]
#[ORM\Index(name: "projecttasktemplates_id", columns: ["projecttasktemplates_id"])]
#[ORM\Index(name: "is_template", columns: ["is_template"])]
#[ORM\Index(name: "is_milestone", columns: ["is_milestone"])]
class Projecttask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $uuid;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(type: 'text', nullable: true)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'projects_id', referencedColumnName: 'id', nullable: true)]
    private ?Project $project;

    #[ORM\ManyToOne(targetEntity: Projecttask::class)]
    #[ORM\JoinColumn(name: 'projecttasks_id', referencedColumnName: 'id', nullable: true)]
    private ?Projecttask $projecttask;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

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

    #[ORM\ManyToOne(targetEntity: Projectstate::class)]
    #[ORM\JoinColumn(name: 'projectstates_id', referencedColumnName: 'id', nullable: true)]
    private ?Projectstate $projectstate;

    #[ORM\ManyToOne(targetEntity: Projecttasktype::class)]
    #[ORM\JoinColumn(name: 'projecttasktypes_id', referencedColumnName: 'id', nullable: true)]
    private ?Projecttasktype $projecttasktype;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $percent_done;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $auto_percent_done;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_milestone;

    #[ORM\ManyToOne(targetEntity: Projecttasktemplate::class)]
    #[ORM\JoinColumn(name: 'projecttasktemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?Projecttasktemplate $projecttasktemplate;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_template;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $template_name;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

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
        return $this->date_mod;
    }

    public function setDateMod(?\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

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

    public function getPercentDone(): ?int
    {
        return $this->percent_done;
    }

    public function setPercentDone(?int $percent_done): self
    {
        $this->percent_done = $percent_done;

        return $this;
    }

    public function getAutoPercentDone(): ?bool
    {
        return $this->auto_percent_done;
    }

    public function setAutoPercentDone(?bool $auto_percent_done): self
    {
        $this->auto_percent_done = $auto_percent_done;

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

    public function getIsTemplate(): ?bool
    {
        return $this->is_template;
    }

    public function setIsTemplate(?bool $is_template): self
    {
        $this->is_template = $is_template;

        return $this;
    }

    public function getTemplateName(): ?string
    {
        return $this->template_name;
    }

    public function setTemplateName(?string $template_name): self
    {
        $this->template_name = $template_name;

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
    public function getProjecttask()
    {
        return $this->projecttask;
    }

    /**
     * Set the value of projecttask
     *
     * @return  self
     */ 
    public function setProjecttask($projecttask)
    {
        $this->projecttask = $projecttask;

        return $this;
    }

    /**
     * Get the value of projectstate
     */ 
    public function getProjectstate()
    {
        return $this->projectstate;
    }

    /**
     * Set the value of projectstate
     *
     * @return  self
     */ 
    public function setProjectstate($projectstate)
    {
        $this->projectstate = $projectstate;

        return $this;
    }

    /**
     * Get the value of projecttasktype
     */ 
    public function getProjecttasktype()
    {
        return $this->projecttasktype;
    }

    /**
     * Set the value of projecttasktype
     *
     * @return  self
     */ 
    public function setProjecttasktype($projecttasktype)
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

    /**
     * Get the value of projecttasktemplate
     */ 
    public function getProjecttasktemplate()
    {
        return $this->projecttasktemplate;
    }

    /**
     * Set the value of projecttasktemplate
     *
     * @return  self
     */ 
    public function setProjecttasktemplate($projecttasktemplate)
    {
        $this->projecttasktemplate = $projecttasktemplate;

        return $this;
    }
}
