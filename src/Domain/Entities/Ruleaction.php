<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_ruleactions')]
#[ORM\Index(name: "rules_id", columns: ["rules_id"])]
#[ORM\Index(name: "field_value", columns: ["field", "value"])]

class Ruleaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Rule::class)]
    #[ORM\JoinColumn(name: 'rules_id', referencedColumnName: 'id', nullable: true)]
    private ?Rule $rule = null;

    #[ORM\Column(name: 'action_type', type: 'string', length: 255, nullable: true, options: ['comment' => 'VALUE IN (assign, regex_result, append_regex_result, affectbyip, affectbyfqdn, affectbymac)'])]
    private $actionType;

    #[ORM\Column(name: 'field', type: 'string', length: 255, nullable: true)]
    private $field;

    #[ORM\Column(name: 'value', type: 'string', length: 255, nullable: true)]
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActionType(): ?string
    {
        return $this->actionType;
    }

    public function setActionType(?string $actionType): self
    {
        $this->actionType = $actionType;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

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
