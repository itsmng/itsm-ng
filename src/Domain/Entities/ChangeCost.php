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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;


    #[ORM\ManyToOne(targetEntity: Change::class)]
    #[ORM\JoinColumn(name: 'changes_id', referencedColumnName: 'id', nullable: true)]
    private ?Change $change = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'comment', type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(name: 'begin_date', type: 'date', nullable: true)]
    private $beginDate;

    #[ORM\Column(name: 'end_date', type: 'date', nullable: true)]
    private $endDate;

    #[ORM\Column(name: 'actiontime', type: 'integer', options: ['default' => 0])]
    private $actiontime;

    #[ORM\Column(name: 'cost_time', type: 'decimal', precision: 20, scale: 4, options: ['default' => "0.0000"])]
    private $costTime;

    #[ORM\Column(name: 'cost_fixed', type: 'decimal', precision: 20, scale: 4, options: ['default' => "0.0000"])]
    private $costFixed;

    #[ORM\Column(name: 'cost_material', type: 'decimal', precision: 20, scale: 4, options: ['default' => "0.0000"])]
    private $costMaterial;

    #[ORM\ManyToOne(targetEntity: Budget::class)]
    #[ORM\JoinColumn(name: 'budgets_id', referencedColumnName: 'id', nullable: true)]
    private ?Budget $budget = null;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

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
        return $this->beginDate;
    }

    public function setBeginDate(\DateTimeInterface $beginDate): self
    {
        $this->beginDate = $beginDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

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
        return $this->costTime;
    }

    public function setCostTime(?float $costTime): self
    {
        $this->costTime = $costTime;

        return $this;
    }

    public function getCostFixed(): ?float
    {
        return $this->costFixed;
    }

    public function setCostFixed(?float $costFixed): self
    {
        $this->costFixed = $costFixed;

        return $this;
    }

    public function getCostMaterial(): ?float
    {
        return $this->costMaterial;
    }

    public function setCostMaterial(?float $costMaterial): self
    {
        $this->costMaterial = $costMaterial;

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
