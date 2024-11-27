<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

/*
Column	Type	Comment
id	int(11) Auto Increment
changes_id	int(11) [0]
problems_id	int(11) [0]
Indexes
PRIMARY	id
UNIQUE	changes_id, problems_id
INDEX	problems_id
 */

#[ORM\Entity]
#[ORM\Table(name: 'glpi_changes_problems')]
#[ORM\UniqueConstraint(columns: ['changes_id', 'problems_id'])]
#[ORM\Index(name: 'problems_id', columns: ['problems_id'])]
class ChangeProblem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', name: 'changes_id', options: ['default' => 0])]
    private $changes_id;

    #[ORM\ManyToOne(targetEntity: Change::class, inversedBy: 'changesProblems')]
    #[ORM\JoinColumn(name: 'changes_id', referencedColumnName: 'id', nullable: false)]
    private ?Change $change;

    #[ORM\Column(type: 'integer', name: 'problems_id', options: ['default' => 0])]
    private $problems_id;

    #[ORM\ManyToOne(targetEntity: Problem::class, inversedBy: 'changesProblems')]
    #[ORM\JoinColumn(name: 'problems_id', referencedColumnName: 'id', nullable: false)]
    private ?Problem $problem;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getChangesId(): int
    {
        return $this->changes_id;
    }

    public function setChangesId(int $changes_id): self
    {
        $this->changes_id = $changes_id;

        return $this;
    }

    public function getProblemsId(): int
    {
        return $this->problems_id;
    }

    public function setProblemsId(int $problems_id): self
    {
        $this->problems_id = $problems_id;

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
     * Get the value of problem
     */ 
    public function getProblem()
    {
        return $this->problem;
    }

    /**
     * Set the value of problem
     *
     * @return  self
     */ 
    public function setProblem($problem)
    {
        $this->problem = $problem;

        return $this;
    }
}
