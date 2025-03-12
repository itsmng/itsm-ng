<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_slalevelactions')]
#[ORM\Index(name: "slalevels_id", columns: ["slalevels_id"])]
class SlaLevelAction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: SlaLevel::class)]
    #[ORM\JoinColumn(name: 'slalevels_id', referencedColumnName: 'id', nullable: true)]
    private ?SlaLevel $slalevel = null;

    #[ORM\Column(name: 'action_type', type: 'string', length: 255, nullable: true)]
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

    public function setActionType(string $actionType): self
    {
        $this->actionType = $actionType;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }


    /**
     * Get the value of slalevel
     */
    public function getSlaLevel()
    {
        return $this->slalevel;
    }

    /**
     * Set the value of slalevel
     *
     * @return  self
     */
    public function setSlaLevel($slalevel)
    {
        $this->slalevel = $slalevel;

        return $this;
    }
}
