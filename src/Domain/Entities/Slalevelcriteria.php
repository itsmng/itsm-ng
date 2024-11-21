<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_slalevelcriterias')]
#[ORM\Index(name: "slalevels_id", columns: ["slalevels_id"])]
#[ORM\Index(name: "condition", columns: ["condition"])]
class Slalevelcriteria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $slalevels_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $criteria;

    #[ORM\Column(type: 'integer', options: ['default' => 0, 'comment' => 'see define.php PATTERN_* and REGEX_* constant'])]
    private $condition;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $pattern;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlalevelsId(): ?int
    {
        return $this->slalevels_id;
    }

    public function setSlalevelsId(int $slalevels_id): self
    {
        $this->slalevels_id = $slalevels_id;

        return $this;
    }

    public function getCriteria(): ?string
    {
        return $this->criteria;
    }

    public function setCriteria(string $criteria): self
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getCondition(): ?int
    {
        return $this->condition;
    }

    public function setCondition(int $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

}
