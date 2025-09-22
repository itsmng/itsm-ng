<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Itsmng\Domain\Entities\Group;

#[ORM\Entity]
#[ORM\Table(name: "glpi_groups_problems")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["problems_id", "type", "groups_id"])]
#[ORM\Index(name: "group", columns: ['groups_id', 'type'])]
class GroupProblem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Problem::class, inversedBy: 'groupProblems')]
    #[ORM\JoinColumn(name: 'problems_id', referencedColumnName: 'id', nullable: true)]
    private ?Problem $problem = null;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'groupProblems')]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group = null;

    #[ORM\Column(name: 'type', type: "integer", options: ['default' => 1])]
    private $type = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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
