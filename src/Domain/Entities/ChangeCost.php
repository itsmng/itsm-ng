<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_changecosts')]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'changes_id', columns: ['changes_id'])]
#[ORM\Index(name: 'begin_date', columns: ['begin_date'])]
#[ORM\Index(name: 'end_date', columns: ['end_date'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
#[ORM\Index(name: 'budgets_id', columns: ['budgets_id'])]
class ChangeCost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $changes_id;

    #[ORM\ManyToOne(targetEntity: Change::class, inversedBy: 'changecosts')]
    #[ORM\JoinColumn(name: 'changes_id', referencedColumnName: 'id', nullable: false)]
    private ?Change $change;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: 'date', nullable: true)]
    private $begin_date;

    #[ORM\Column(type: 'date', nullable: true)]
    private $end_date;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $actiontime;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, options: ['default' => 0.0])]
    private $cost_time;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, options: ['default' => 0.0])]
    private $cost_fixed;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, options: ['default' => 0.0])]
    private $cost_material;

    #[ORM\Column(type: 'integer', name: 'budgets_id', options: ['default' => 0])]
    private $budgets_id;

    #[ORM\ManyToOne(targetEntity: Budget::class, inversedBy: 'changecosts')]
    #[ORM\JoinColumn(name: 'budgets_id', referencedColumnName: 'id', nullable: false)]
    private ?Budget $budget;

    #[ORM\Column(type: 'integer', name: 'entities_id', options: ['default' => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class, inversedBy: 'changecosts')]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    public function getBeginDate(): ?\DateTimeInterface
    {
        return $this->begin_date;
    }

    public function setBeginDate(\DateTimeInterface $begin_date): self
    {
        $this->begin_date = $begin_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;

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

    public function getCostTime(): ?float
    {
        return $this->cost_time;
    }

    public function setCostTime(?float $cost_time): self
    {
        $this->cost_time = $cost_time;

        return $this;
    }

    public function getCostFixed(): ?float
    {
        return $this->cost_fixed;
    }

    public function setCostFixed(?float $cost_fixed): self
    {
        $this->cost_fixed = $cost_fixed;

        return $this;
    }

    public function getCostMaterial(): ?float
    {
        return $this->cost_material;
    }

    public function setCostMaterial(?float $cost_material): self
    {
        $this->cost_material = $cost_material;

        return $this;
    }

    public function getBudgetsId(): ?int
    {
        return $this->budgets_id;
    }

    public function setBudgetsId(?int $budgets_id): self
    {
        $this->budgets_id = $budgets_id;

        return $this;
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
     * Get the value of budget
     */ 
    public function getBudget()
    {
        return $this->budget;
    }

    /**
     * Set the value of budget
     *
     * @return  self
     */ 
    public function setBudget($budget)
    {
        $this->budget = $budget;

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
}
