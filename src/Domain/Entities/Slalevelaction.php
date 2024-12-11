<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_slalevelactions')]
#[ORM\Index(name: "slalevels_id", columns: ["slalevels_id"])]
class Slalevelaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Slalevel::class)]
    #[ORM\JoinColumn(name: 'slalevels_id', referencedColumnName: 'id', nullable: true)]
    private ?Slalevel $slalevel;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $action_type;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $field;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActionType(): ?string
    {
        return $this->action_type;
    }

    public function setActionType(string $action_type): self
    {
        $this->action_type = $action_type;

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
    public function getSlalevel()
    {
        return $this->slalevel;
    }

    /**
     * Set the value of slalevel
     *
     * @return  self
     */
    public function setSlalevel($slalevel)
    {
        $this->slalevel = $slalevel;

        return $this;
    }
}
