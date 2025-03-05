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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Rule::class)]
    #[ORM\JoinColumn(name: 'rules_id', referencedColumnName: 'id', nullable: true)]
    private ?Rule $rule = null;

    #[ORM\Column(name: 'criteria', type: 'string', length: 255, nullable: true)]
    private $criteria;

    #[ORM\Column(name: 'condition', type: 'integer', options: ['default' => 0, 'comment' => 'see define.php PATTERN_* and REGEX_* constant'])]
    private $condition;

    #[ORM\Column(name: 'pattern', type: 'text', length: 65535, nullable: true)]
    private $pattern;



    public function getId(): ?int
    {
        return $this->id;
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


    /**
     * Get the value of rule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Set the value of rule
     *
     * @return  self
     */
    public function setRule($rule)
    {
        $this->rule = $rule;

        return $this;
    }
}
