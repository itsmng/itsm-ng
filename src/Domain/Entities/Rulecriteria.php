<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_rulecriterias')]
#[ORM\Index(name: "rules_id", columns: ["rules_id"])]
#[ORM\Index(name: "condition", columns: ["condition"])]
class Rulecriteria  
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $rules_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $criteria;

    #[ORM\Column(type: 'integer', options: ['default' => 0, 'comment' => 'see define.php PATTERN_* and REGEX_* constant'])]
    private $condition;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $pattern;

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRulesId(): ?string
    {
        return $this->rules_id;
    }

    public function setRulesId(?string $rules_id): self
    {
        $this->rules_id = $rules_id;

        return $this;
    }

    public function getCriteria(): ?string
    {
        return $this->criteria;
    }

    public function setCriteria(?string $criteria): self
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(?string $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setPattern(?string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

}
