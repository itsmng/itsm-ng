<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_changes_problems')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['changes_id', 'problems_id'])]
#[ORM\Index(name: 'problems_id', columns: ['problems_id'])]
class ChangeProblem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Change::class, inversedBy: 'changeProblems')]
    #[ORM\JoinColumn(name: 'changes_id', referencedColumnName: 'id', nullable: true)]
    private ?Change $change = null;

    #[ORM\ManyToOne(targetEntity: Problem::class, inversedBy: 'changeProblems')]
    #[ORM\JoinColumn(name: 'problems_id', referencedColumnName: 'id', nullable: true)]
    private ?Problem $problem = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }



    /**
     * Get the value of change
     */
    public function getChange(): ?Change
    {
        return $this->change;
    }

    /**
     * Set the value of change
     *
     * @return  self
     */
    public function setChange(?Change $change): self
    {
        $this->change = $change;

        return $this;
    }

    /**
     * Get the value of problem
     */
    public function getProblem(): ?Problem
    {
        return $this->problem;
    }

    /**
     * Set the value of problem
     *
     * @return  self
     */
    public function setProblem(Problem $problem): self
    {
        $this->problem = $problem;

        return $this;
    }
}
