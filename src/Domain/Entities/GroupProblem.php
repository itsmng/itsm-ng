<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Group;

#[ORM\Entity]
#[ORM\Table(name: "glpi_groups_problems")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["problems_id", "type", "groups_id"])]
#[ORM\Index(name: "group", columns: ['groups_id', 'type'])]
class GroupProblem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", name: 'problems_id', options: ['default' => 0])]
    private $problems_id;

    #[ORM\ManyToOne(targetEntity: Problem::class, inversedBy: 'groupProblems')]
    #[ORM\JoinColumn(name: 'problems_id', referencedColumnName: 'id', nullable: false)]
    private ?Problem $problem;

    #[ORM\Column(type: "integer", options: ['default' => 0])]
    private $groups_id;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'groupProblems')]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: false)]
    private ?Group $group;

    #[ORM\Column(type: "integer", options: ['default' => 1])]
    private $type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProblemsId(): ?int
    {
        return $this->problems_id;
    }

    public function setProblemsId(int $knowbaseitems_id): self
    {
        $this->problems_id = $knowbaseitems_id;

        return $this;
    }

    public function getGroupsId(): ?int
    {
        return $this->groups_id;
    }

    public function setGroupsId(int $groups_id): self
    {
        $this->groups_id = $groups_id;

        return $this;
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
