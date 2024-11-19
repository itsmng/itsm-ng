<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_problemcosts')]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "problems_id", columns: ["problems_id"])]
#[ORM\Index(name: "begin_date", columns: ["begin_date"])]
#[ORM\Index(name: "end_date", columns: ["end_date"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "budgets_id", columns: ["budgets_id"])]
class Problemcost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options:['default' => 0])]
    private $problems_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'date', nullable: true)]
    private $begin_date;

    #[ORM\Column(type: 'date', nullable: true)]
    private $end_date;

    #[ORM\Column(type: 'integer', options:['default' => 0])]
    private $actiontime;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, options:['default' => 0.0000])]
    private $cost_time;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, options:['default' => 0.0000])]
    private $cost_fixed;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, options:['default' => 0.0000])]
    private $cost_material;

    #[ORM\Column(type: 'integer', options:['default' => 0])]
    private $budgets_id;

    #[ORM\Column(type: 'integer', options:['default' => 0])]
    private $entities_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProblemsId(): ?int
    {
        return $this->problems_id;
    }

    public function setProblemsId(?int $problems_id): self
    {
        $this->problems_id = $problems_id;

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

}
