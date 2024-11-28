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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $changes_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $problems_id;

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
}
