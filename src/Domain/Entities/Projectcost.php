<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_projectcosts')]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "projects_id", columns: ["projects_id"])]
#[ORM\Index(name: "begin_date", columns: ["begin_date"])]
#[ORM\Index(name: "end_date", columns: ["end_date"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "budgets_id", columns: ["budgets_id"])]
class Projectcost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'projects_id', referencedColumnName: 'id', nullable: true)]
    private ?Project $project;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'date', nullable: true)]
    private $begin_date;

    #[ORM\Column(type: 'date', nullable: true)]
    private $end_date;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, options: ['default' => 0.0000])]
    private $cost;

    #[ORM\ManyToOne(targetEntity: Budget::class)]
    #[ORM\JoinColumn(name: 'budgets_id', referencedColumnName: 'id', nullable: true)]
    private ?Budget $budget;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

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
        return $this->begin_date;
    }


    public function setBeginDate(?\DateTimeInterface $begin_date): self
    {
        $this->begin_date = $begin_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }


    public function setEndDate(?\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }


    public function setCost(?float $cost): self
    {
        $this->cost = $cost;

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
